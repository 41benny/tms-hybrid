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
        // In some environments, the original create_* migrations for these
        // tables were marked as "ran" manually without actually creating
        // the tables. This migration safely creates them only if missing.

        if (!Schema::hasTable('payment_receipts')) {
            Schema::create('payment_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_number', 50)->unique();
                $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
                $table->date('payment_date');

                // Payment Details
                $table->decimal('amount', 15, 2);
                $table->enum('payment_method', ['bank_transfer', 'cash', 'check', 'giro', 'other']);
                $table->string('reference_number', 100)->nullable();

                // Allocation tracking
                $table->decimal('allocated_amount', 15, 2)->default(0);

                // Metadata
                $table->text('notes')->nullable();
                // Link to company cash/bank account master
                $table->foreignId('bank_account_id')->nullable()->constrained('cash_bank_accounts')->onDelete('set null');
                $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');

                $table->timestamps();

                $table->index('customer_id');
                $table->index('payment_date');
            });
        }

        if (!Schema::hasTable('invoice_payments')) {
            Schema::create('invoice_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
                $table->foreignId('payment_receipt_id')->constrained('payment_receipts')->onDelete('cascade');
                $table->decimal('allocated_amount', 15, 2);

                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index('invoice_id');
                $table->index('payment_receipt_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to satisfy foreign key constraints when rolling back.
        if (Schema::hasTable('invoice_payments')) {
            Schema::drop('invoice_payments');
        }

        if (Schema::hasTable('payment_receipts')) {
            Schema::drop('payment_receipts');
        }
    }
};
