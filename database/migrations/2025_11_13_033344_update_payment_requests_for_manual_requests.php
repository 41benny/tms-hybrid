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
            // Make vendor_bill_id nullable (untuk manual payment request)
            $table->foreignId('vendor_bill_id')->nullable()->change();

            // Add vendor_id untuk manual payment (jika tidak dari vendor bill)
            $table->foreignId('vendor_id')->nullable()->after('vendor_bill_id')->constrained('vendors')->nullOnDelete();

            // Add payment_type untuk membedakan manual vs vendor bill
            $table->enum('payment_type', ['vendor_bill', 'manual'])->default('vendor_bill')->after('vendor_bank_account_id');

            // Add description untuk manual payment
            $table->string('description')->nullable()->after('payment_type');

            $table->index('vendor_id');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['payment_type']);

            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'payment_type', 'description']);

            // Revert vendor_bill_id to NOT NULL (if needed)
            // Note: This might fail if there are nullable values
        });
    }
};
