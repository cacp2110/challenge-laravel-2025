<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // FK a orders: requiere que la tabla orders exista antes
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();

            $table->string('description');
            $table->unsignedInteger('quantity');          // en PostgreSQL "unsigned" es ignorado, pero sirve semánticamente
            $table->decimal('unit_price', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
