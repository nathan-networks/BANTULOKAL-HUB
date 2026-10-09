<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\EquipmentSubmission;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $counts = [
            'pending_service_requests' => ServiceRequest::where(
                'status',
                'pending'
            )->count(),

            'pending_freelancers' => ServiceProvider::where(
                'status',
                'pending'
            )->count(),

            'pending_bookings' => Booking::where(
                'status',
                'pending'
            )->count(),

            'pending_equipment_submissions' => EquipmentSubmission::where(
                'status',
                'submitted'
            )->count(),

            'published_equipment' => Equipment::where(
                'status',
                'published'
            )->count(),
        ];

        $recentServiceRequests = ServiceRequest::with('category')
            ->latest()
            ->limit(5)
            ->get();

        $recentFreelancers = ServiceProvider::latest()
            ->limit(5)
            ->get();

        $recentBookings = Booking::with('equipment')
            ->latest()
            ->limit(5)
            ->get();

        $recentEquipmentSubmissions = EquipmentSubmission::with('category')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'counts',
            'recentServiceRequests',
            'recentFreelancers',
            'recentBookings',
            'recentEquipmentSubmissions',
        ));
    }
}
