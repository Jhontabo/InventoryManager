<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (! Schema::hasIndex('bookings', 'bookings_status_start_at_index')) {
                $table->index(['status', 'start_at']);
            }

            if (! Schema::hasIndex('bookings', 'bookings_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }

            if (! Schema::hasIndex('bookings', 'bookings_schedule_id_status_index')) {
                $table->index(['schedule_id', 'status']);
            }
        });

        Schema::table('schedules', function (Blueprint $table): void {
            if (! Schema::hasColumn('schedules', 'recurrence_until')) {
                return;
            }

            if (! Schema::hasIndex('schedules', 'schedules_recurrence_until_index')) {
                $table->index('recurrence_until');
            }

            if (! Schema::hasIndex('schedules', 'schedules_type_start_at_index')) {
                $table->index(['type', 'start_at']);
            }

            if (! Schema::hasIndex('schedules', 'schedules_type_laboratory_id_start_at_index')) {
                $table->index(['type', 'laboratory_id', 'start_at']);
            }
        });

        Schema::table('loans', function (Blueprint $table): void {
            if (! Schema::hasIndex('loans', 'loans_status_requested_at_index')) {
                $table->index(['status', 'requested_at']);
            }

            if (! Schema::hasIndex('loans', 'loans_status_estimated_return_at_index')) {
                $table->index(['status', 'estimated_return_at']);
            }

            if (! Schema::hasIndex('loans', 'loans_status_actual_return_at_index')) {
                $table->index(['status', 'actual_return_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (Schema::hasIndex('bookings', 'bookings_status_start_at_index')) {
                $table->dropIndex('bookings_status_start_at_index');
            }

            if (Schema::hasIndex('bookings', 'bookings_status_created_at_index')) {
                $table->dropIndex('bookings_status_created_at_index');
            }

            if (Schema::hasIndex('bookings', 'bookings_schedule_id_status_index')) {
                $table->dropIndex('bookings_schedule_id_status_index');
            }
        });

        Schema::table('schedules', function (Blueprint $table): void {
            if (Schema::hasIndex('schedules', 'schedules_recurrence_until_index')) {
                $table->dropIndex('schedules_recurrence_until_index');
            }

            if (Schema::hasIndex('schedules', 'schedules_type_start_at_index')) {
                $table->dropIndex('schedules_type_start_at_index');
            }

            if (Schema::hasIndex('schedules', 'schedules_type_laboratory_id_start_at_index')) {
                $table->dropIndex('schedules_type_laboratory_id_start_at_index');
            }
        });

        Schema::table('loans', function (Blueprint $table): void {
            if (Schema::hasIndex('loans', 'loans_status_requested_at_index')) {
                $table->dropIndex('loans_status_requested_at_index');
            }

            if (Schema::hasIndex('loans', 'loans_status_estimated_return_at_index')) {
                $table->dropIndex('loans_status_estimated_return_at_index');
            }

            if (Schema::hasIndex('loans', 'loans_status_actual_return_at_index')) {
                $table->dropIndex('loans_status_actual_return_at_index');
            }
        });
    }
};
