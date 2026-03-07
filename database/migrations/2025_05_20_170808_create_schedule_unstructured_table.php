<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_unstructured', function (Blueprint $table) {
            $table->id();

            $table->foreignId('schedule_id')
                ->constrained('schedules')
                ->cascadeOnDelete();

            $table->string('project_type')->nullable();  // nullable para integradores, grados...
            $table->string('academic_program')->nullable();  // nullable para programa académico
            $table->unsignedTinyInteger('semester')->nullable();  // 1 al 10, ahora nullable
            $table->string('applicants')->nullable();  // nullable para nombres
            $table->string('research_name')->nullable()->index(); // nullable + index
            $table->string('advisor')->nullable();  // nullable para asesor

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_unstructured');
    }
};
