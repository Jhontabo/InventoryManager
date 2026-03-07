<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del usuario
            $table->string('last_name'); // Apellido del usuario
            $table->string('email')->unique(); // Correo único
            $table->string('password'); // Contraseña para autenticación
            $table->string('phone')->nullable(); // Teléfono opcional
            $table->string('address'); // Dirección
            $table->string('document_number')->nullable(); // Dirección
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->rememberToken(); // Token para recordar sesión
            $table->timestamps(); // Timestamps created_at y updated_at
        });
    }

    public function down()
    {
        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if (Schema::hasTable('laboratories')) {
            Schema::table('laboratories', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }
        Schema::dropIfExists('users');
    }
};
