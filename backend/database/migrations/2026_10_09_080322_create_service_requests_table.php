<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            // Customer information
            $table->string('customer_name', 150);
            $table->string('customer_email');
            $table->string('customer_whatsapp', 30);
            $table->string('location', 255);

            // Requested service
            $table->foreignId('category_id')
                ->constrained('service_categories')
                ->restrictOnDelete();

            $table->text('description');
            $table->unsignedTinyInteger('worker_count')->default(1);
            $table->date('preferred_date')->nullable();

            // Pricing: amounts stored in rupiah
            $table->decimal('estimated_total', 12, 2)->nullable();
            $table->decimal('final_total', 12, 2)->nullable();
            $table->decimal('worker_pool_amount', 12, 2)->nullable();
            $table->decimal('platform_amount', 12, 2)->nullable();

            // Request review and fulfilment
            $table->string('status', 30)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Terms acceptance
            $table->string('terms_version', 50);
            $table->timestampTz('terms_accepted_at');

            // Payment tracking
            $table->string('payment_status', 30)->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->string('invoice_number')->nullable();

            // Communication and completion timestamps
            $table->timestampTz('acknowledgement_sent_at')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('completed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['category_id', 'status']);
            $table->index(['payment_status', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
