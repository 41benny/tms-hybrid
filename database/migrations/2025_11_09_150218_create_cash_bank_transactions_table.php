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
        Schema::dropIfExists('cash_bank_transactions');
        Schema::create('cash_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_bank_account_id')->constrained('cash_bank_accounts');
            $table->date('tanggal');
            $table->string('jenis'); // cash_in | cash_out
            $table->string('sumber'); // customer_payment | vendor_payment | expense | other_in | other_out
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('vendor_bill_id')->nullable()->constrained('vendor_bills')->nullOnDelete();
            $table->foreignId('coa_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['cash_bank_account_id', 'tanggal'], 'cb_trx_acc_date_idx');
            $table->index(['invoice_id', 'vendor_bill_id'], 'cb_trx_doc_idx');
            $table->index(['coa_id'], 'cb_trx_coa_idx');
            $table->index(['customer_id'], 'cb_trx_cust_idx');
            $table->index(['vendor_id'], 'cb_trx_vendor_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_bank_transactions');
    }
};
