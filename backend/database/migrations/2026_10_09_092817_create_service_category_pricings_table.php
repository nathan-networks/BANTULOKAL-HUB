<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_category_pricings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->unique()
                ->constrained('service_categories')
                ->cascadeOnDelete();

            $table->string('pricing_method', 30);
            $table->decimal('base_price', 12, 2);
            $table->decimal('included_quantity', 8, 2)->nullable();
            $table->string('included_unit', 30)->nullable();
            $table->decimal('extra_unit_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_category_pricings');
    }
};