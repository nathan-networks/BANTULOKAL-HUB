<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('equipment_owner_id')
                ->constrained('equipment_owners')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['equipment_owner_id']);
            $table->dropColumn('equipment_owner_id');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->constrained('users')
                ->restrictOnDelete();
        });
    }
};