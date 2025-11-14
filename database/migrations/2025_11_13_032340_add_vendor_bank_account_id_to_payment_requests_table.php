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
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->foreignId('vendor_bank_account_id')->nullable()->after('vendor_bill_id')->constrained('vendor_bank_accounts')->nullOnDelete();
            $table->index('vendor_bank_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropForeign(['vendor_bank_account_id']);
            $table->dropIndex(['vendor_bank_account_id']);
            $table->dropColumn('vendor_bank_account_id');
        });
    }
};
