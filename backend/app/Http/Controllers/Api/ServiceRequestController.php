<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
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

        $serviceRequest = ServiceRequest::create([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_whatsapp' => $data['customer_whatsapp'],
            'category_id' => $category->id,
            'description' => $data['description'],
            'location' => $data['location'],
            'worker_count' => $data['worker_count'],
            'preferred_date' => $data['preferred_date'] ?? null,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'terms_version' => 'service-request-v1',
            'terms_accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Permintaan berhasil dikirim untuk ditinjau.',
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'payment_status' => $serviceRequest->payment_status,
            ],
        ], 201);
    }
}
