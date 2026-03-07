<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('equipment_decommissions', function (Blueprint $table) {
            // ID principal
            $table->id();

            // 1. RELACIÓN CON EL PRODUCTO
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('ID del producto dado de baja');

            // 2. TIPO Y RAZÓN DE BAJA
            $table->enum('reason', [
                'damaged',      // Equipo dañado
                'maintenance',  // En mantenimiento
                'lost',         // Equipo perdido
                'obsolete',     // Obsoleto
                'other'         // Otros motivos
            ])->comment('Tipo principal de baja');

            $table->enum('damage_type', [
                'student',
                'usage',
                'manufacturing',
                'other'

            ])->nullable();

            $table->text('details')
                ->nullable()
                ->comment('Detalles adicionales del motivo');

            // 3. RESPONSABLE (usuario con rol de estudiante)
            $table->foreignId('responsible_user_id')
                ->nullable()
                ->constrained('users')
                ->comment('Usuario con rol de estudiante responsable');

            // 4. INFORMACIÓN ACADÉMICA (solo para estudiantes)
            $table->string('student_document')
                ->nullable()
                ->comment('Documento de identidad del estudiante');

            $table->string('academic_program')
                ->nullable()
                ->comment('Programa académico del estudiante');

            $table->string('semester')
                ->nullable()
                ->comment('Semestre actual del estudiante');

            // 5. FECHAS IMPORTANTES
            $table->date('decommission_date')
                ->useCurrent()
                ->comment('Fecha en que se dio de baja el equipo');

            $table->date('expected_return_date')
                ->nullable()
                ->comment('Fecha esperada de retorno (para mantenimiento)');

            // 6. REGISTRO DE ACCIONES
            $table->foreignId('registered_by')
                ->constrained('users')
                ->comment('Usuario que registró la baja');

            $table->foreignId('reversed_by')
                ->nullable()
                ->constrained('users')
                ->comment('Usuario que revirtió la baja');

            $table->timestamp('reversed_at')
                ->nullable()
                ->comment('Fecha en que se revirtió la baja');

            // 7. OBSERVACIONES
            $table->text('observations')
                ->nullable()
                ->comment('Observaciones adicionales sobre la baja');

            // Timestamps automáticos
            $table->timestamps();

            // ÍNDICES PARA OPTIMIZACIÓN
            $table->index('product_id');
            $table->index('reason');
            $table->index('decommission_date');
            $table->index('responsible_user_id');
            $table->index('registered_by');
            $table->index('reversed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('equipment_decommissions', function (Blueprint $table) {
            // Eliminar todas las claves foráneas primero
            $table->dropForeign(['product_id']);
            $table->dropForeign(['responsible_user_id']);
            $table->dropForeign(['registered_by']);
            $table->dropForeign(['reversed_by']);
        });

        // Luego eliminar la tabla
        Schema::dropIfExists('equipment_decommissions');
    }
};
