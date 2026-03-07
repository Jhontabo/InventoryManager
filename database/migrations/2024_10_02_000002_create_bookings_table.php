<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
  public function up()
  {
    Schema::create('bookings', function (Blueprint $table) {
      $table->id();

      // Datos personales (del usuario autenticado)
      $table->string('name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('email')->nullable();

      // Motivo de rechazo
      $table->text('rejection_reason')->nullable();

      // Relaciones
      $table->foreignId('schedule_id')
        ->constrained('schedules')
        ->cascadeOnDelete();
      $table->foreignId('user_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();
      $table->foreignId('laboratory_id')
        ->nullable()
        ->constrained('laboratories')
        ->cascadeOnDelete();

      // Datos de la solicitud
      $table->string('project_type')->nullable();
      $table->string('academic_program')->nullable();
      $table->unsignedTinyInteger('semester')->nullable();
      $table->text('applicants')->nullable();
      $table->string('research_name')->nullable();
      $table->string('advisor')->nullable();
      $table->json('products')->nullable();

      // Horario solicitado
      $table->dateTime('start_at')->nullable();
      $table->dateTime('end_at')->nullable();

      // Color opcional
      $table->string('color')->default('#4b82f6');

      // Estado de la solicitud
      $table->enum('status', ['pending', 'approved', 'reserved', 'rejected'])
        ->default('pending');

      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('bookings');
  }
};
