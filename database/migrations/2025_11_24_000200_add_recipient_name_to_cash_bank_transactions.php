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
        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('reference_number')
                ->comment('Nama penerima (cash out) atau pengirim (cash in)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            $table->dropColumn('recipient_name');
        });
    }
};
