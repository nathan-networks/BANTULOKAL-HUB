
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('whatsapp', 30);
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('active');

            $table->timestamps();

            $table->index('whatsapp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
