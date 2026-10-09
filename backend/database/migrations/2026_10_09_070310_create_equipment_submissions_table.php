<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_submissions', function (Blueprint $table) {
            $table->id();

            $table->string('owner_name', 150);
            $table->string('whatsapp', 30);
            $table->string('email')->nullable();

            $table->string('equipment_name', 150);
            $table->text('description')->nullable();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('equipment_categories')
                ->nullOnDelete();

            $table->decimal('proposed_price_per_day', 12, 2)->nullable();
            $table->decimal('proposed_deposit_amount', 12, 2)
                ->nullable();

            $table->string('location')->nullable();

            $table->string('status', 30)->default('submitted');
            $table->text('admin_notes')->nullable();

            $table->timestampTz('contacted_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('whatsapp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_submissions');
    }
};