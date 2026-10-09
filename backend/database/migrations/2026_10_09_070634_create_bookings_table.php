
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->restrictOnDelete();

            // Customers do not need accounts.
            $table->string('customer_name', 150);
            $table->string('customer_email');
            $table->string('customer_whatsapp', 30);

            $table->date('rental_start_date');
            $table->date('rental_end_date');

            // Snapshot the requested rental price.
            $table->decimal('price_per_day', 12, 2);
            $table->decimal('total_amount', 12, 2);

            $table->string('status', 30)->default('pending');
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestampTz('contacted_at')->nullable();

            // Authorized administrator handling the request.
            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index([
                'equipment_id',
                'rental_start_date',
                'rental_end_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
