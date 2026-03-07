<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('recurrence_days')->nullable()->after('color');
            $table->date('recurrence_until')->nullable()->after('recurrence_days');
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['recurrence_days', 'recurrence_until']);
        });
    }
};
