<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users - agregar índice en status si no existe
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasIndex('users', 'users_status_index')) {
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_status_index')) {
                $table->dropIndex('users_status_index');
            }
        });
    }
};
