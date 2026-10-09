<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FreelancerApplicationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'whatsapp' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'skills' => ['required', 'string', 'max:5000'],
            'experience' => ['nullable', 'string', 'max:5000'],
        ]);

        $application = ServiceProvider::create([
            'name' => $data['name'],
            'whatsapp' => $data['whatsapp'],
            'email' => $data['email'] ?? null,
            'skills' => $data['skills'],
            'experience' => $data['experience'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil dikirim. Tim BANTULOKAL HUB akan meninjau aplikasi Anda.',
            'data' => [
                'application_id' => $application->id,
                'status' => $application->status,
            ],
        ], 201);
    }
}
