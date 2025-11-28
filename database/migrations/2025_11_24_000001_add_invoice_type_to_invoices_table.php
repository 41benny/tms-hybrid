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
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['normal', 'down_payment', 'progress', 'final'])
                ->default('normal')
                ->after('status');
            
            // Kolom untuk tracking invoice DP yang dipotong (untuk invoice final)
            $table->foreignId('related_invoice_id')
                ->nullable()
                ->after('invoice_type')
                ->constrained('invoices')
                ->onDelete('set null')
                ->comment('ID invoice DP yang dipotong (untuk invoice final)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['related_invoice_id']);
            $table->dropColumn(['invoice_type', 'related_invoice_id']);
        });
    }
};
