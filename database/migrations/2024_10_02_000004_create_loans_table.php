<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key to products
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Foreign key to users
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Loan data
            $table->dateTime('requested_at'); // Date when the loan was requested
            $table->dateTime('approved_at')->nullable(); // Approval date
            $table->dateTime('estimated_return_at')->nullable(); // Estimated return date
            $table->dateTime('actual_return_at')->nullable(); // Actual return date

            // Loan status
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'returned',
                'overdue'
            ])->default('pending');

            // Optional observations
            $table->text('observations')->nullable();

            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans');
    }
};

