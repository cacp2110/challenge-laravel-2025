<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('status')->default('initiated'); // initiated|sent|delivered
            $table->timestamps();

            $table->index('status'); // útil para /api/orders activos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
