<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        return DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]) !== [];
    }

    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (! $this->indexExists('bookings', 'bookings_status_index')) {
                $table->index('status');
            }
            if (! $this->indexExists('bookings', 'bookings_laboratory_id_start_at_index')) {
                $table->index(['laboratory_id', 'start_at']);
            }
            if (! $this->indexExists('bookings', 'bookings_user_id_index')) {
                $table->index('user_id');
            }
        });

        Schema::table('loans', function (Blueprint $table): void {
            if (! $this->indexExists('loans', 'loans_status_index')) {
                $table->index('status');
            }
            if (! $this->indexExists('loans', 'loans_product_id_index')) {
                $table->index('product_id');
            }
            if (! $this->indexExists('loans', 'loans_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! $this->indexExists('products', 'products_available_for_loan_index')) {
                $table->index('available_for_loan');
            }
            if (! $this->indexExists('products', 'products_status_index')) {
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if ($this->indexExists('bookings', 'bookings_status_index')) {
                $table->dropIndex('bookings_status_index');
            }
            if ($this->indexExists('bookings', 'bookings_laboratory_id_start_at_index')) {
                $table->dropIndex('bookings_laboratory_id_start_at_index');
            }
            if ($this->indexExists('bookings', 'bookings_user_id_index')) {
                $table->dropIndex('bookings_user_id_index');
            }
        });

        Schema::table('loans', function (Blueprint $table): void {
            if ($this->indexExists('loans', 'loans_status_index')) {
                $table->dropIndex('loans_status_index');
            }
            if ($this->indexExists('loans', 'loans_product_id_index')) {
                $table->dropIndex('loans_product_id_index');
            }
            if ($this->indexExists('loans', 'loans_user_id_status_index')) {
                $table->dropIndex('loans_user_id_status_index');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if ($this->indexExists('products', 'products_available_for_loan_index')) {
                $table->dropIndex('products_available_for_loan_index');
            }
            if ($this->indexExists('products', 'products_status_index')) {
                $table->dropIndex('products_status_index');
            }
        });
    }
};
