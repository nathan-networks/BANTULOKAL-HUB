<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\ServiceCategoryPricing;
use Illuminate\Database\Seeder;

class ServiceCategoryPricingSeeder extends Seeder
{
    public function run(): void
    {
        $pricing = [
            'bersih-bersih' => [
                'pricing_method' => 'per_worker',
                'base_price' => 120000,
                'included_quantity' => 2,
                'included_unit' => 'hours',
                'extra_unit_price' => null,
            ],
            'pindah-kos' => [
                'pricing_method' => 'per_worker',
                'base_price' => 100000,
                'included_quantity' => 2,
                'included_unit' => 'hours',
                'extra_unit_price' => null,
            ],
            'desain-poster' => [
                'pricing_method' => 'per_project',
                'base_price' => 125000,
                'included_quantity' => null,
                'included_unit' => null,
                'extra_unit_price' => null,
            ],
            'jasa-beli-barang' => [
                'pricing_method' => 'per_order',
                'base_price' => 20000,
                'included_quantity' => 3,
                'included_unit' => 'items',
                'extra_unit_price' => null,
            ],
            'jasa-antar-barang' => [
                'pricing_method' => 'per_trip',
                'base_price' => 25000,
                'included_quantity' => 3,
                'included_unit' => 'km',
                'extra_unit_price' => 3000,
            ],
            'jasa-fotografi' => [
                'pricing_method' => 'per_session',
                'base_price' => 250000,
                'included_quantity' => 1,
                'included_unit' => 'hours',
                'extra_unit_price' => null,
            ],
        ];

        foreach ($pricing as $slug => $values) {
            $category = ServiceCategory::where('slug', $slug)->first();

            if (! $category) {
                throw new \RuntimeException(
                    "Service category '{$slug}' was not found. Run the category seeder first."
                );
            }

            ServiceCategoryPricing::updateOrCreate(
                ['category_id' => $category->id],
                array_merge($values, ['is_active' => true])
            );
        }
    }
}
