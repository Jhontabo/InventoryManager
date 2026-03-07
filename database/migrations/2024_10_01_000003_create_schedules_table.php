<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            // Campos del horario
            $table->string('title');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('color')->default('#3b82f6');
            $table->text('description')->nullable();
            $table->enum('type', ['structured', 'unstructured'])->default('structured');

            // Relaciones
            $table->foreignId('laboratory_id')
                ->nullable()
                ->constrained('laboratories')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            // Índices
            $table->index('type');
            $table->index('start_at');
            $table->index('end_at');
            $table->index(['laboratory_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
