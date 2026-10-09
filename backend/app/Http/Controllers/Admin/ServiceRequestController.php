<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ServiceRequestPaymentInstructions;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Mail\ServiceRequestPaymentConfirmation;
use Illuminate\Support\Facades\Mail;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::with('category')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.service-requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load('category', 'reviewer', 'paymentVerifier');

        return view(
            'admin.service-requests.show',
            compact('serviceRequest')
        );
    }

    public function review(
        Request $request,
        ServiceRequest $serviceRequest
    ) {
        $data = $request->validate([
            'decision' => [
                'required',
                Rule::in(['approved', 'rejected']),
            ],
            'service_fee' => [
                'required_if:decision,approved',
                'nullable',
                'numeric',
                'min:1',
                'max:9999999999.99',
            ],
            'approved_expenses' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],
            'expense_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'admin_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        if ($serviceRequest->status !== 'pending') {
            return back()->withErrors([
                'decision' => 'Permintaan ini sudah ditinjau sebelumnya.',
            ]);
        }

        $serviceRequest->status = $data['decision'];
        $serviceRequest->admin_notes = $data['admin_notes'] ?? null;
        $serviceRequest->reviewed_by = $request->user()->id;

        if ($data['decision'] === 'approved') {
            $serviceFee = round((float) $data['service_fee'], 2);
            $approvedExpenses = round(
                (float) ($data['approved_expenses'] ?? 0),
                2
            );

            $serviceRequest->service_fee = $serviceFee;
            $serviceRequest->approved_expenses = $approvedExpenses;
            $serviceRequest->expense_notes = $data['expense_notes'] ?? null;

            $serviceRequest->final_total = round(
                $serviceFee + $approvedExpenses,
                2
            );

            // The 80/20 split applies only to the service fee.
            // Approved additional expenses are excluded.
            $serviceRequest->worker_pool_amount = round(
                $serviceFee * 0.80,
                2
            );

            $serviceRequest->platform_amount = round(
                $serviceFee * 0.20,
                2
            );

            $serviceRequest->approved_at = now();
        } else {
            $serviceRequest->approved_at = null;
        }

        $serviceRequest->save();

        $emailSent = false;

        if ($data['decision'] === 'approved') {
            try {
                $url = config('services.apps_script_email.url');
                $token = config('services.apps_script_email.token');

                if (!$url || !$token) {
                    throw new \RuntimeException(
                        'Apps Script email settings are missing.'
                    );
                }

                $serviceRequest->load('category');

                $mail = new ServiceRequestPaymentInstructions(
                    $serviceRequest
                );

                $response = Http::asJson()
                    ->timeout(30)
                    ->post($url, [
                        'token' => $token,
                        'to' => $serviceRequest->customer_email,
                        'subject' => $mail->envelope()->subject,
                        'html' => $mail->render(),
                    ]);

                $response->throw();

                $result = $response->json();

                if (($result['success'] ?? false) !== true) {
                    throw new \RuntimeException(
                        'Apps Script did not confirm email success: ' .
                        json_encode($result)
                    );
                }

                $emailSent = true;
            } catch (\Throwable $exception) {
                Log::error(
                    'Service request payment instructions email failed.',
                    [
                        'service_request_id' => $serviceRequest->id,
                        'exception' => $exception->getMessage(),
                    ]
                );
            }
        }

        if ($data['decision'] === 'rejected') {
            return redirect()
                ->route('admin.service-requests.show', $serviceRequest)
                ->with('success', 'Permintaan jasa berhasil ditolak.');
        }

        return redirect()
            ->route('admin.service-requests.show', $serviceRequest)
            ->with(
                $emailSent ? 'success' : 'error',
                $emailSent
                    ? 'Permintaan disetujui. Instruksi pembayaran berhasil dikirim ke email pelanggan.'
                    : 'Permintaan disetujui dan harga final disimpan, tetapi email instruksi pembayaran gagal dikirim. Periksa log Laravel.'
            );
    }


            public function verifyPayment(
                Request $request,
                ServiceRequest $serviceRequest
            ) {
                $data = $request->validate([
                    'payment_reference' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],
                ]);

                $result = DB::transaction(function () use (
                    $request,
                    $serviceRequest,
                    $data
                ) {
                    $record = ServiceRequest::whereKey($serviceRequest->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($record->status !== 'approved') {
                        return [
                            'success' => false,
                            'message' => 'Hanya permintaan yang disetujui yang dapat diverifikasi pembayarannya.',
                        ];
                    }

                    if ($record->payment_status !== 'unpaid') {
                        return [
                            'success' => false,
                            'message' => 'Pembayaran ini sudah diproses sebelumnya.',
                        ];
                    }

                    if ($record->final_total === null) {
                        return [
                            'success' => false,
                            'message' => 'Total pembayaran final belum ditentukan.',
                        ];
                    }

                    $record->payment_status = 'paid';
                    $record->payment_reference = $data['payment_reference'] ?? null;
                    $record->paid_at = now();
                    $record->payment_verified_by = $request->user()->id;

                    if (!$record->invoice_number) {
                        $record->invoice_number = 'BLH-' . now()->format('Ymd') . '-' .
                            str_pad(
                                (string) $record->id,
                                6,
                                '0',
                                STR_PAD_LEFT
                            );
                    }

                    $record->save();

                    return [
                        'success' => true,
                        'invoice_number' => $record->invoice_number,
                        'service_request_id' => $record->id,
                    ];
                });

                if (!$result['success']) {
                    return back()->withErrors([
                        'payment' => $result['message'],
                    ]);
                }

                // Send the confirmation email after the payment transaction commits.
                $emailSent = false;

                try {
                    $record = ServiceRequest::with('category')
                        ->findOrFail($result['service_request_id']);

                    $mail = new ServiceRequestPaymentConfirmation($record);

                    $response = Http::asJson()
                        ->timeout(30)
                        ->post(config('services.apps_script_email.url'), [
                            'token' => config('services.apps_script_email.token'),
                            'to' => $record->customer_email,
                            'subject' => $mail->envelope()->subject,
                            'html' => $mail->render(),
                        ]);

                    $responseData = $response->json();

                    if (
                        $response->successful()
                        && is_array($responseData)
                        && ($responseData['success'] ?? false) === true
                    ) {
                        $emailSent = true;
                    } else {
                        Log::error('Payment confirmation email was not accepted by Apps Script.', [
                            'service_request_id' => $record->id,
                            'http_status' => $response->status(),
                            'response' => $response->body(),
                        ]);
                    }
                } catch (\Throwable $exception) {
                    Log::error('Failed to send payment confirmation email.', [
                        'service_request_id' => $result['service_request_id'],
                        'error' => $exception->getMessage(),
                    ]);
                }

                $message = 'Pembayaran berhasil diverifikasi. Nomor invoice: ' .
                    $result['invoice_number'];

                if ($emailSent) {
                    $message .= ' Email konfirmasi pembayaran berhasil dikirim.';
                } else {
                    $message .= ' Namun, email konfirmasi belum berhasil dikirim. Periksa log aplikasi sebelum mencoba mengirim ulang.';
                }

                return redirect()
                    ->route('admin.service-requests.show', $serviceRequest)
                    ->with('success', $message);
            }
                public function resendPaymentConfirmation(
                    ServiceRequest $serviceRequest
                ) {
                    if ($serviceRequest->payment_status !== 'paid') {
                        return back()->withErrors([
                            'payment_confirmation_email' =>
                                'Email konfirmasi hanya dapat dikirim ulang untuk pembayaran yang sudah terverifikasi.',
                        ]);
                    }

                    try {
                        $serviceRequest->load('category');

                        $mail = new \App\Mail\ServiceRequestPaymentConfirmation(
                            $serviceRequest
                        );

                        $response = Http::asJson()
                            ->timeout(30)
                            ->post(config('services.apps_script_email.url'), [
                                'token' => config('services.apps_script_email.token'),
                                'to' => $serviceRequest->customer_email,
                                'subject' => $mail->envelope()->subject,
                                'html' => $mail->render(),
                            ]);

                        $responseData = $response->json();

                        if (
                            $response->successful()
                            && is_array($responseData)
                            && ($responseData['success'] ?? false) === true
                        ) {
                            Log::info('Payment confirmation email resent successfully.', [
                                'service_request_id' => $serviceRequest->id,
                                'invoice_number' => $serviceRequest->invoice_number,
                            ]);

                            return back()->with(
                                'success',
                                'Email konfirmasi pembayaran berhasil dikirim ulang ke pelanggan.'
                            );
                        }

                        Log::error('Payment confirmation email resend was rejected.', [
                            'service_request_id' => $serviceRequest->id,
                            'http_status' => $response->status(),
                            'response' => $response->body(),
                        ]);
                    } catch (\Throwable $exception) {
                        Log::error('Payment confirmation email resend failed.', [
                            'service_request_id' => $serviceRequest->id,
                            'error' => $exception->getMessage(),
                        ]);
                    }

                    return back()->withErrors([
                        'payment_confirmation_email' =>
                            'Pembayaran tetap tercatat lunas, tetapi email konfirmasi gagal dikirim. Periksa log aplikasi dan coba lagi.',
                    ]);

            }

}
