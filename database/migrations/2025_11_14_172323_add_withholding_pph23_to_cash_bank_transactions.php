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
            $table->decimal('withholding_pph23', 15, 2)
                ->default(0)
                ->after('amount')
                ->comment('Potongan PPh 23 dari penerimaan customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            $table->dropColumn('withholding_pph23');
        });
    }
};
