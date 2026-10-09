<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\RentalBookingReceived;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
            $validator = Validator::make($request->all(), [
                'equipment_id' => ['required', 'integer', 'exists:equipment,id'],
                'customer_name' => ['required', 'string', 'max:150'],
                'customer_email' => ['required', 'email', 'max:255'],
                'customer_whatsapp' => ['required', 'string', 'max:30'],
                'rental_start_date' => [
                    'required',
                    'date_format:Y-m-d',
                    'after_or_equal:today',
                ],
                'rental_end_date' => [
                    'required',
                    'date_format:Y-m-d',
                    'after_or_equal:rental_start_date',
                ],
                'customer_notes' => ['nullable', 'string', 'max:2000'],

                'terms_accepted' => ['required', 'accepted'],
                'terms_version' => ['required', 'in:rental-terms-v1'],
            ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data pengajuan sewa tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $equipment = Equipment::query()
            ->where('status', 'published')
            ->findOrFail($data['equipment_id']);

        // Rental duration counts both the start and end dates.
        $start = new \DateTimeImmutable($data['rental_start_date']);
        $end = new \DateTimeImmutable($data['rental_end_date']);

        $days = (int) $start->diff($end)->days + 1;

        // Store the requested price as a snapshot.
        $priceInCents = (int) round(
            (float) $equipment->price_per_day * 100
        );

        $totalInCents = $priceInCents * $days;

        $booking = Booking::create([
            'equipment_id' => $equipment->id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_whatsapp' => $data['customer_whatsapp'],
            'rental_start_date' => $data['rental_start_date'],
            'rental_end_date' => $data['rental_end_date'],
            'price_per_day' => number_format(
                $priceInCents / 100,
                2,
                '.',
                ''
            ),
            'total_amount' => number_format(
                $totalInCents / 100,
                2,
                '.',
                ''
            ),
            'status' => 'pending',
            'customer_notes' => $data['customer_notes'] ?? null,
            'terms_version' => $data['terms_version'],
            'terms_accepted_at' => now(),
        ]);

            // Send acknowledgement after the booking has been saved.
            try {
                $url = config('services.apps_script_email.url');
                $token = config('services.apps_script_email.token');

                if (! $url || ! $token) {
                    throw new \RuntimeException(
                        'Apps Script email settings are missing.'
                    );
                }

                $booking->load('equipment');

                $mail = new RentalBookingReceived($booking);

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

                $booking->acknowledgement_sent_at = now();
                $booking->save();
            } catch (\Throwable $exception) {
                Log::error('Rental booking acknowledgement email failed.', [
                    'booking_id' => $booking->id,
                    'exception' => $exception->getMessage(),
                ]);
            }

        return response()->json([
            'message' => 'Pengajuan sewa berhasil dikirim.',
            'booking' => [
                'id' => $booking->id,
                'equipment_name' => $equipment->name,
                'rental_start_date' => $booking->rental_start_date
                    ->toDateString(),
                'rental_end_date' => $booking->rental_end_date
                    ->toDateString(),
                'rental_days' => $days,
                'price_per_day' => $booking->price_per_day,
                'total_amount' => $booking->total_amount,
                'deposit_amount' => $equipment->deposit_amount,
                'status' => $booking->status,
            ],
        ], 201);
    }
}
