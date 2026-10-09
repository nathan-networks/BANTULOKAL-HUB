<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $equipment = Equipment::query()
            ->where('status', 'published')
            ->with([
                'category:id,name,slug',
                'images' => function ($query) {
                    $query->orderByDesc('is_primary')
                        ->orderBy('sort_order');
                },
            ])
            ->when(
                $request->filled('category'),
                function ($query) use ($request) {
                    $query->whereHas('category', function ($category) use ($request) {
                        $category->where('slug', $request->query('category'));
                    });
                }
            )
            ->orderByDesc('created_at')
            ->paginate(12);

        $equipment->getCollection()->transform(function ($item) {
            $primaryImage = $item->images->first();

            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price_per_day' => $item->price_per_day,
                'deposit_amount' => $item->deposit_amount,
                'condition' => $item->condition,
                'location' => $item->location,
                'category' => $item->category,
                'image_url' => $primaryImage
                    ? Storage::disk('public')->url($primaryImage->path)
                    : null,
            ];
        });

        return response()->json($equipment);
    }

    public function show(int $id): JsonResponse
    {
        $item = Equipment::query()
            ->where('status', 'published')
            ->with(['category', 'images'])
            ->findOrFail($id);

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price_per_day' => $item->price_per_day,
            'deposit_amount' => $item->deposit_amount,
            'condition' => $item->condition,
            'location' => $item->location,
            'category' => $item->category,
            'images' => $item->images->map(fn ($image) => [
                'url' => Storage::disk('public')->url($image->path),
                'alt_text' => $image->alt_text,
                'is_primary' => $image->is_primary,
            ]),
        ]);
    }
}
