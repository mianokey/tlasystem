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
        Schema::create('student_fee_invoices', function (Blueprint $table) {
            $table->id();

            // ✅ CHANGE HERE
            $table->foreignId('student_id')
                ->constrained('students')
                ->onDelete('cascade');

            $table->foreignId('session_year_id')
                ->constrained('session_years')
                ->onDelete('cascade');

            $table->string('invoice_no')->unique();

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);

            $table->enum('status', ['unpaid', 'partial', 'paid'])
                ->default('unpaid');

            $table->date('due_date')->nullable();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_invoices');
    }
};
