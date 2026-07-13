<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {

            $table->foreignId('student_id')
                ->nullable()
                ->after('id')
                ->constrained('students')
                ->nullOnDelete();

            $table->foreignId('student_fee_invoice_item_id')
                ->nullable()
                ->after('student_id')
                ->constrained('student_fee_invoice_items')
                ->nullOnDelete();

            $table->string('bank_name')
                ->nullable()
                ->after('payment_gateway');

            $table->date('payment_date')
                ->nullable()
                ->after('bank_name');

            $table->string('received_by')
                ->nullable()
                ->after('payment_date');

        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {

            $table->dropConstrainedForeignId('student_fee_invoice_item_id');
            $table->dropConstrainedForeignId('student_id');

            $table->dropColumn([
                'bank_name',
                'payment_date',
                'received_by',
            ]);

        });
    }
};