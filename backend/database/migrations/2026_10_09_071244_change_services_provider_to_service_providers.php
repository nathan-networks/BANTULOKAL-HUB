
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropIndex('services_provider_id_status_index');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('provider_id')
                ->references('id')
                ->on('service_providers')
                ->restrictOnDelete();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropIndex('services_provider_id_status_index');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('provider_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index(['provider_id', 'status']);
        });
    }
};
