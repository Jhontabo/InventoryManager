<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('schedule_details'); // Eliminar si existe

        // Crear nueva versión simplificada para notas adicionales
        Schema::create('schedule_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->text('private_notes')->nullable(); // Para observaciones internas
            $table->text('public_notes')->nullable(); // Para comentarios visibles
            $table->timestamps();

            $table->index('schedule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_notes');
    }
};
