
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Admin-approved rental pricing.
            $table->decimal('final_rental_amount', 12, 2)->nullable();
            $table->decimal('final_deposit_amount', 12, 2)->nullable();

            // Manual bank-transfer payment tracking.
            $table->string('payment_status', 30)->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->foreignId('payment_verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('invoice_number', 50)->nullable()->unique();

            // Email delivery tracking.
            $table->timestampTz('acknowledgement_sent_at')->nullable();
            $table->timestampTz('payment_instructions_sent_at')->nullable();
            $table->timestampTz('payment_confirmation_sent_at')->nullable();

            // Rental lifecycle timestamps.
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('rejected_at')->nullable();
            $table->timestampTz('handover_at')->nullable();
            $table->timestampTz('returned_at')->nullable();
            $table->timestampTz('closed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['payment_verified_by']);
            $table->dropUnique(['invoice_number']);

            $table->dropColumn([
                'final_rental_amount',
                'final_deposit_amount',
                'payment_status',
                'payment_reference',
                'paid_at',
                'payment_verified_by',
                'invoice_number',
                'acknowledgement_sent_at',
                'payment_instructions_sent_at',
                'payment_confirmation_sent_at',
                'approved_at',
                'rejected_at',
                'handover_at',
                'returned_at',
                'closed_at',
            ]);
        });
    }
};
