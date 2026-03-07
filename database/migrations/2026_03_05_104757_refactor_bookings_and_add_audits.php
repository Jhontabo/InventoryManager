<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (Schema::hasColumn('bookings', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('bookings', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('bookings', 'email')) {
                $table->dropColumn('email');
            }

            if (! Schema::hasColumn('bookings', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('laboratories', function (Blueprint $table): void {
            if (! Schema::hasColumn('laboratories', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('laboratories', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (! Schema::hasColumn('bookings', 'name')) {
                $table->string('name')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'email')) {
                $table->string('email')->nullable();
            }

            if (Schema::hasColumn('bookings', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('bookings', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });

        Schema::table('laboratories', function (Blueprint $table): void {
            if (Schema::hasColumn('laboratories', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('laboratories', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });
    }
};
