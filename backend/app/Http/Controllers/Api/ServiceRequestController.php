<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryPricing;
use App\Models\ServiceRequest;
use App\Mail\ServiceRequestReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_whatsapp' => ['required', 'string', 'max:30'],
            'category' => [
                'required',
                'string',
                Rule::exists('service_categories', 'slug'),
            ],
            'description' => ['required', 'string', 'max:5000'],
            'location' => ['required', 'string', 'max:255'],
            'worker_count' => ['required', 'integer', 'min:1', 'max:4'],
            'preferred_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'terms_accepted' => ['accepted'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Periksa kembali data permintaan Anda.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $category = ServiceCategory::where(
            'slug',
            $data['category']
        )->firstOrFail();

        $pricing = ServiceCategoryPricing::where(
            'category_id',
            $category->id
        )
            ->where('is_active', true)
            ->first();

        if (! $pricing) {
            return response()->json([
                'message' => 'Harga layanan ini belum tersedia. Silakan hubungi tim kami.',
            ], 422);
        }

        // Only cleaning and moving services multiply the rate by workers.
        // Other services use a single project, order, trip, or session rate.
        $workerCount = $pricing->pricing_method === 'per_worker'
            ? (int) $data['worker_count']
            : 1;

        $estimatedTotal = (float) $pricing->base_price * $workerCount;

        $serviceRequest = ServiceRequest::create([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_whatsapp' => $data['customer_whatsapp'],
            'category_id' => $category->id,
            'description' => $data['description'],
            'location' => $data['location'],
            'worker_count' => $workerCount,
            'preferred_date' => $data['preferred_date'] ?? null,
            'estimated_total' => $estimatedTotal,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'terms_version' => 'service-request-v1',
            'terms_accepted_at' => now(),
        ]);

        // Send acknowledgement only after the request has been saved.
        try {
            $url = config('services.apps_script_email.url');
            $token = config('services.apps_script_email.token');

            if (!$url || !$token) {
                throw new \RuntimeException(
                    'Apps Script email settings are missing.'
                );
            }

            $serviceRequest->load('category');

            $mail = new ServiceRequestReceived($serviceRequest);
            $subject = $mail->envelope()->subject;

            $response = Http::asJson()
                ->timeout(30)
                ->post($url, [
                    'token' => $token,
                    'to' => $serviceRequest->customer_email,
                    'subject' => $subject,
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

            $serviceRequest->acknowledgement_sent_at = now();
            $serviceRequest->save();
        } catch (\Throwable $exception) {
            Log::error('Service request acknowledgement email failed.', [
                'service_request_id' => $serviceRequest->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Permintaan berhasil dikirim untuk ditinjau.',
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'payment_status' => $serviceRequest->payment_status,
                'category' => $category->slug,
                'pricing_method' => $pricing->pricing_method,
                'worker_count' => $workerCount,
                'estimated_total' => $serviceRequest->estimated_total,
                'currency' => 'IDR',
                'estimate_note' => 'Estimasi awal. Biaya akhir dan pengeluaran tambahan akan dikonfirmasi oleh tim sebelum pembayaran.',
            ],
        ], 201);
    }
}
