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
        Schema::create('invoice_transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('cash_bank_transaction_id')->constrained('cash_bank_transactions')->onDelete('cascade');
            $table->decimal('amount_paid', 15, 2)->comment('Jumlah yang dibayar');
            $table->date('payment_date')->comment('Tanggal pembayaran');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('invoice_id');
            $table->index('cash_bank_transaction_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_transaction_payments');
    }
};
