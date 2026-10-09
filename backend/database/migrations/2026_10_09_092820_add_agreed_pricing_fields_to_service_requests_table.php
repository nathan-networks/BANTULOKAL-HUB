<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->decimal('approved_expenses', 12, 2)->default(0);
            $table->text('expense_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'service_fee',
                'approved_expenses',
                'expense_notes',
            ]);
        });
    }
};