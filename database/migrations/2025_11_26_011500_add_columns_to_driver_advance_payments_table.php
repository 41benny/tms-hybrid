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
        Schema::table('driver_advance_payments', function (Blueprint $table) {
            $table->foreignId('driver_advance_id')->after('id')->constrained('driver_advances')->onDelete('cascade');
            $table->foreignId('cash_bank_transaction_id')->after('driver_advance_id')->constrained('cash_bank_transactions')->onDelete('cascade');
            $table->decimal('amount_paid', 15, 2)->after('cash_bank_transaction_id');
            $table->date('payment_date')->after('amount_paid');
            $table->text('notes')->nullable()->after('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_advance_payments', function (Blueprint $table) {
            $table->dropForeign(['driver_advance_id']);
            $table->dropForeign(['cash_bank_transaction_id']);
            $table->dropColumn(['driver_advance_id', 'cash_bank_transaction_id', 'amount_paid', 'payment_date', 'notes']);
        });
    }
};
