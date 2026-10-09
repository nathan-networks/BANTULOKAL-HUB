<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategoryPricing;
use Illuminate\Http\JsonResponse;

class ServicePricingController extends Controller
{
    public function index(): JsonResponse
    {
        $pricing = ServiceCategoryPricing::query()
            ->with('category:id,name,slug')
            ->where('is_active', true)
            ->get()
            ->map(function (ServiceCategoryPricing $item) {
                return [
                    'category' => $item->category->name,
                    'slug' => $item->category->slug,
                    'pricing_method' => $item->pricing_method,
                    'base_price' => (float) $item->base_price,
                    'included_quantity' => $item->included_quantity,
                    'included_unit' => $item->included_unit,
                    'extra_unit_price' => $item->extra_unit_price,
                ];
            })
            ->values();

        return response()->json([
            'data' => $pricing,
            'currency' => 'IDR',
        ]);
    }
}
