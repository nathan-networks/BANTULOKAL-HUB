<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Mail\RentalBookingPaymentInstructions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\RentalBookingPaymentConfirmation;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('equipment')
            ->latest()
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['equipment', 'handler', 'paymentVerifier']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function review(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'decision' => [
                'required',
                Rule::in(['approved', 'rejected']),
            ],
            'final_rental_amount' => [
                'required_if:decision,approved',
                'nullable',
                'numeric',
                'min:0',
            ],
            'final_deposit_amount' => [
                'required_if:decision,approved',
                'nullable',
                'numeric',
                'min:0',
            ],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($booking->status !== 'pending') {
            return back()->withErrors([
                'decision' => 'Pengajuan ini sudah ditinjau sebelumnya.',
            ]);
        }

        DB::transaction(function () use ($booking, $data, $request) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ($lockedBooking->status !== 'pending') {
                abort(409, 'Pengajuan ini sudah ditinjau sebelumnya.');
            }

            $lockedBooking->handled_by = $request->user()->id;
            $lockedBooking->admin_notes = $data['admin_notes'] ?? null;

            if ($data['decision'] === 'approved') {
                $lockedBooking->status = 'approved';
                $lockedBooking->final_rental_amount =
                    $data['final_rental_amount'];
                $lockedBooking->final_deposit_amount =
                    $data['final_deposit_amount'];
                $lockedBooking->approved_at = now();
            } else {
                $lockedBooking->status = 'rejected';
                $lockedBooking->rejected_at = now();
            }

            $lockedBooking->save();
        });

        // Send payment instructions only after approval has been committed.
        if ($request->input('decision') === 'approved') {
            try {
                $url = config('services.apps_script_email.url');
                $token = config('services.apps_script_email.token');

                if (! $url || ! $token) {
                    throw new \RuntimeException(
                        'Apps Script email settings are missing.'
                    );
                }

                $booking->refresh();
                $booking->load('equipment');

                $mail = new RentalBookingPaymentInstructions($booking);

                $response = Http::asJson()
                    ->timeout(30)
                    ->post($url, [
                        'token' => $token,
                        'to' => $booking->customer_email,
                        'subject' => $mail->envelope()->subject,
                        'html' => $mail->render(),
                    ]);

                $response->throw();

                $result = $response->json();

                if (($result['success'] ?? false) !== true) {
                    throw new \RuntimeException(
                        'Apps Script did not confirm success: ' .
                        json_encode($result)
                    );
                }

                $booking->payment_instructions_sent_at = now();
                $booking->save();
            } catch (\Throwable $exception) {
                Log::error('Rental payment instructions email failed.', [
                    'booking_id' => $booking->id,
                    'exception' => $exception->getMessage(),
                ]);

                return redirect()
                    ->route('admin.bookings.show', $booking)
                    ->with('warning',
                        'Sewa disetujui, tetapi email instruksi pembayaran gagal dikirim. Periksa log aplikasi.'
                    );
            }
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with(
                'success',
                $data['decision'] === 'approved'
                    ? 'Pengajuan sewa disetujui.'
                    : 'Pengajuan sewa ditolak.'
            );
    }


    public function verifyPayment(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $verified = DB::transaction(function () use ($booking, $validated) {
            $locked = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if (
                $locked->status !== 'approved' ||
                $locked->payment_status === 'paid' ||
                $locked->final_rental_amount === null ||
                $locked->final_deposit_amount === null
            ) {
                return false;
            }

            $locked->payment_status = 'paid';
            $locked->payment_reference = $validated['payment_reference'] ?? null;
            $locked->paid_at = now();
            $locked->payment_verified_by = auth()->id();

            if (! $locked->invoice_number) {
                $locked->invoice_number = 'BLH-' .
                    now()->format('Ymd') . '-' .
                    str_pad((string) $locked->id, 6, '0', STR_PAD_LEFT);
            }

            $locked->save();

            return true;
        });

        if (! $verified) {
            return back()->withErrors([
                'payment' => 'Sewa harus disetujui dan belum dibayar sebelum pembayaran dapat diverifikasi.',
            ]);
        }

        $booking->refresh();

        if (! $this->sendPaymentConfirmation($booking)) {
            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('warning',
                    'Pembayaran tercatat, tetapi email konfirmasi gagal dikirim. Gunakan tombol kirim ulang.'
                );
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Pembayaran berhasil diverifikasi dan email konfirmasi terkirim.');
    }

    public function resendPaymentConfirmation(Booking $booking)
    {
        if ($booking->payment_status !== 'paid') {
            return back()->withErrors([
                'payment' => 'Konfirmasi hanya dapat dikirim ulang setelah pembayaran diverifikasi.',
            ]);
        }

        if (! $this->sendPaymentConfirmation($booking)) {
            return back()->withErrors([
                'email' => 'Email konfirmasi gagal dikirim. Periksa log aplikasi.',
            ]);
        }

        return back()->with('success', 'Email konfirmasi pembayaran berhasil dikirim ulang.');
    }

    private function sendPaymentConfirmation(Booking $booking): bool
    {
        try {
            $url = config('services.apps_script_email.url');
            $token = config('services.apps_script_email.token');

            if (! $url || ! $token) {
                throw new \RuntimeException(
                    'Apps Script email settings are missing.'
                );
            }

            $booking->load('equipment');

            $mail = new RentalBookingPaymentConfirmation($booking);

            $response = Http::asJson()
                ->timeout(30)
                ->post($url, [
                    'token' => $token,
                    'to' => $booking->customer_email,
                    'subject' => $mail->envelope()->subject,
                    'html' => $mail->render(),
                ]);

            $response->throw();

            $result = $response->json();

            if (($result['success'] ?? false) !== true) {
                throw new \RuntimeException(
                    'Apps Script did not confirm success: ' .
                    json_encode($result)
                );
            }

            $booking->payment_confirmation_sent_at = now();
            $booking->save();

            return true;
        } catch (\Throwable $exception) {
            Log::error('Rental payment confirmation email failed.', [
                'booking_id' => $booking->id,
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
    }

}
