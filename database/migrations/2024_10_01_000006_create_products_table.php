<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            // ID principal
            $table->id();

            // --- 1. DATOS GENERALES ---
            $table->string('name')->comment('Nombre del producto/equipo');
            $table->text('description')->nullable()->comment('Descripción detallada del producto');
            $table->unsignedInteger('available_quantity')->default(6)->comment('Cantidad disponible en inventario');
            $table->string('serial_number')->nullable()->unique()->comment('Número de serie del equipo');
            $table->decimal('unit_cost', 16, 2)->nullable()->comment('Costo unitario del equipo');
            $table->string('location')->nullable()->comment('Ubicación física del equipo');
            $table->date('acquisition_date')->nullable()->comment('Fecha de adquisición del equipo');

            // NUEVOS CAMPOS DATOS GENERALES
            $table->string('use')->nullable()->comment('Uso del equipo');
            $table->json('applies_to')->nullable()->comment('Procesos al que aplica (ej: investigación, docencia)');
            $table->json('authorized_personnel')->nullable()->comment('Personal autorizado para su uso');
            $table->string('brand')->nullable()->comment('Marca');
            $table->string('model')->nullable()->comment('Modelo');
            $table->string('manufacturer')->nullable()->comment('Fabricante');
            $table->enum('calibration_frequency', ['semanal', 'mensual', 'semestral', 'anual'])->nullable()->comment('Frecuencia de calibración');

            // --- 2. DATOS ESPECIFICOS (opcionales) ---
            $table->string('upper_measure')->nullable()->comment('Medida superior');
            $table->string('lower_measure')->nullable()->comment('Medida inferior');
            $table->string('associated_software')->nullable()->comment('Software asociado al equipo');
            $table->string('user_manual')->nullable()->comment('Ruta o URL del manual de usuario');
            $table->string('dimensions')->nullable()->comment('Dimensiones');
            $table->string('weight')->nullable()->comment('Peso');
            $table->string('power')->nullable()->comment('Potencia');
            $table->json('accessories')->nullable()->comment('Accesorios');

            // --- 3. CONDICIONES TOLERABLES PARA USO DEL EQUIPO (opcionales) ---
            $table->float('min_temperature')->nullable()->comment('Temperatura mínima tolerable');
            $table->float('max_temperature')->nullable()->comment('Temperatura máxima tolerable');
            $table->float('min_humidity')->nullable()->comment('Humedad mínima tolerable');
            $table->float('max_humidity')->nullable()->comment('Humedad máxima tolerable');
            $table->float('min_voltage')->nullable()->comment('Voltaje mínimo tolerable');
            $table->float('max_voltage')->nullable()->comment('Voltaje máximo tolerable');

            // --- 4. OBSERVACIONES ---
            $table->text('observations')->nullable()->comment('Observaciones');

            // --- 5. RELACIONES ---
            $table->foreignId('laboratory_id')->constrained('laboratories')->cascadeOnDelete()->comment('Laboratorio al que pertenece el equipo');

            // --- 6. ESTADO Y TIPO ---
            $table->enum('product_type', [
                'equipment',    // Equipo permanente
                'supply',       // Suministro consumible
                'consumable'    // Material de consumo
            ])->default('equipment')->comment('Tipo de producto');

            $table->enum('status', [
                'new',
                'used',
                'damaged',
                'decommissioned',
                'lost',
                'maintenance'
            ])->default('new')->comment('Estado actual del producto');

            $table->boolean('available_for_loan')->default(true)->comment('¿Disponible para préstamos?');

            // --- 7. REGISTRO DE BAJA ---
            $table->timestamp('decommissioned_at')->nullable()->comment('Fecha en que se dio de baja el equipo');
            $table->foreignId('decommissioned_by')->nullable()->constrained('users')->comment('Usuario que dio de baja el equipo');

            // --- 8. AUDITORÍA ---
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('Usuario que creó el registro');
            $table->foreignId('updated_by')->nullable()->constrained('users')->comment('Usuario que actualizó el registro');

            // --- 9. MEDIA ---
            $table->string('image')->nullable()->comment('Imagen del producto');

            // --- 10. MARCAS DE TIEMPO ---
            $table->timestamps();
            $table->softDeletes()->comment('Fecha de eliminación (soft delete)');

            // --- 11. ÍNDICES ---
            $table->index('name');
            $table->index('serial_number');
            $table->index('available_for_loan');
            $table->index('status');
            $table->index('decommissioned_at');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Eliminar las claves foráneas primero
            $table->dropForeign(['laboratory_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['decommissioned_by']);
        });

        Schema::dropIfExists('products');
    }
};
