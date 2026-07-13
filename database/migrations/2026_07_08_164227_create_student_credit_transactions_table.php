<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_credit_transactions', function (Blueprint $table) {

            $table->id();

            // Student
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Payment that generated or consumed the credit
            $table->foreignId('payment_transaction_id')
                ->nullable()
                ->constrained('payment_transactions')
                ->nullOnDelete();

            // Invoice where credit was applied
            $table->foreignId('student_fee_invoice_id')
                ->nullable()
                ->constrained('student_fee_invoices')
                ->nullOnDelete();

            // User performing the transaction
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Credit movement
            $table->enum('type', [
                'overpayment',
                'invoice_application',
                'refund',
                'adjustment'
            ]);

            // Amount moved (+/-)
            $table->decimal('amount', 12, 2);

            // Running balance after this transaction
            $table->decimal('balance_after', 12, 2);

            // Optional narration
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_credit_transactions');
    }
};