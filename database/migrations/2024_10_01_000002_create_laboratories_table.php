<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('name')->nullable(); // Nombre del laboratorio
            $table->string('location')->nullable(); // Ubicación del laboratorio
            $table->unsignedInteger('capacity')->nullable(); // Capacidad máxima del laboratorio

            // Clave foránea para el usuario asignado al laboratorio
            $table->foreignId('user_id')
                ->nullable() // Permitir que no haya usuario asignado inicialmente
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate(); // Actualizar clave si el usuario es actualizado

            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    public function down()
    {

        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropForeign(['laboratory_id']);
            });
        }
        Schema::dropIfExists('laboratories'); // Eliminar la tabla laboratorios
    }
};
