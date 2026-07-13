<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('student_fee_invoice_items', function (Blueprint $table) {

            $table->decimal('paid_amount', 12, 2)
                ->default(0)
                ->after('amount');

            $table->decimal('balance', 12, 2)
                ->default(0)
                ->after('paid_amount');

        });
    }

    public function down(): void
    {
        Schema::table('student_fee_invoice_items', function (Blueprint $table) {

            $table->dropColumn([
                'paid_amount',
                'balance'
            ]);

        });
    }
};
