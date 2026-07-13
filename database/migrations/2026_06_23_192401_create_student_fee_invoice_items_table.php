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
      Schema::create('student_fee_invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_fee_invoice_id')
                ->constrained('student_fee_invoices')
                ->onDelete('cascade');

            $table->foreignId('fees_id')
                ->constrained('fees')
                ->onDelete('cascade');

            $table->foreignId('fees_type_id')
                ->constrained('fees_types')
                ->onDelete('cascade');

            $table->decimal('amount', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_invoice_items');
    }
};
