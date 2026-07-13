<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {

            $table->foreignId('student_fee_invoice_id')
                ->nullable()
                ->after('user_id')
                ->constrained('student_fee_invoices')
                ->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_fee_invoice_id');
        });
    }
};
