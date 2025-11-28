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
        Schema::table('cash_bank_accounts', function (Blueprint $table) {
            $table->string('bank_code', 10)->nullable()->after('code')
                ->comment('Kode bank untuk voucher number (e.g., MDM, BCA, KSB)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_bank_accounts', function (Blueprint $table) {
            $table->dropColumn('bank_code');
        });
    }
};
