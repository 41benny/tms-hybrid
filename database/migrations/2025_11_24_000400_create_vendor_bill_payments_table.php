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
        Schema::create('vendor_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->onDelete('cascade');
            $table->foreignId('cash_bank_transaction_id')->constrained('cash_bank_transactions')->onDelete('cascade');
            $table->decimal('amount_paid', 15, 2)->comment('Jumlah yang dibayar');
            $table->date('payment_date')->comment('Tanggal pembayaran');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('vendor_bill_id');
            $table->index('cash_bank_transaction_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_bill_payments');
    }
};
