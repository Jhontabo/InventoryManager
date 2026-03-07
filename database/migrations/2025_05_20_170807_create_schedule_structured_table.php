<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schedule_structured', function (Blueprint $table) {
            $table->id();

            $table->foreignId('schedule_id')
                ->constrained('schedules')
                ->cascadeOnDelete();

            // Nuevos campos en lugar de clave foránea
            $table->string('academic_program_name');
            $table->integer('semester');
            $table->integer('student_count');
            $table->integer('group_count');

            $table->timestamps();

            // Índices para mejor performance
            $table->index('semester');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_structured');
    }
};
