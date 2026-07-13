<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('student_fee_invoices', function (Blueprint $table) {
        $table->foreignId('semester_id')
            ->nullable()
            ->after('session_year_id')
            ->constrained('semesters')
            ->cascadeOnUpdate()
            ->restrictOnDelete();
    });
}



public function down()
{
    Schema::table('student_fee_invoices', function (Blueprint $table) {
        $table->dropForeign(['semester_id']);
        $table->dropColumn('semester_id');
    });
}
};
