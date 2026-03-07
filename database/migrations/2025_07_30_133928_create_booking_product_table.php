<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_product', function (Blueprint $table) {
            $table->id();

            // Claves foráneas
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Datos específicos de la relación
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('pending');

            $table->timestamps();

            // Índices para mejor rendimiento
            $table->index(['booking_id', 'product_id']);
            $table->index(['status', 'start_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_product');
    }
};
