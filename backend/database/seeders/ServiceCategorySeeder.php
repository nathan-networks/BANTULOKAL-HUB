<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bersih-bersih',
                'slug' => 'bersih-bersih',
                'description' => 'Jasa membersihkan kamar, kos, rumah, dan ruang lainnya.',
            ],
            [
                'name' => 'Pindah kos',
                'slug' => 'pindah-kos',
                'description' => 'Bantuan mengemas, mengangkat, dan memindahkan barang kos.',
            ],
            [
                'name' => 'Desain poster',
                'slug' => 'desain-poster',
                'description' => 'Jasa desain poster untuk acara, promosi, dan kebutuhan lainnya.',
            ],
            [
                'name' => 'Jasa beli barang',
                'slug' => 'jasa-beli-barang',
                'description' => 'Bantuan membeli barang sesuai permintaan pelanggan.',
            ],
            [
                'name' => 'Jasa antar barang',
                'slug' => 'jasa-antar-barang',
                'description' => 'Jasa mengantarkan barang ke lokasi tujuan.',
            ],
            [
                'name' => 'Jasa fotografi',
                'slug' => 'jasa-fotografi',
                'description' => 'Jasa fotografi untuk acara, produk, potret, dan kebutuhan lainnya.',
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
