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
            $table->enum('tax_invoice_status', ['none', 'requested', 'completed'])
                  ->default('none')
                  ->after('status');
            $table->string('tax_invoice_number')->nullable()->unique()->after('tax_invoice_status');
            $table->date('tax_invoice_date')->nullable()->after('tax_invoice_number');
            $table->timestamp('tax_requested_at')->nullable()->after('tax_invoice_date');
            $table->foreignId('tax_requested_by')->nullable()->constrained('users')->after('tax_requested_at');
            $table->timestamp('tax_completed_at')->nullable()->after('tax_requested_by');
            $table->foreignId('tax_completed_by')->nullable()->constrained('users')->after('tax_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['tax_requested_by']);
            $table->dropForeign(['tax_completed_by']);
            $table->dropColumn([
                'tax_invoice_status',
                'tax_invoice_number',
                'tax_invoice_date',
                'tax_requested_at',
                'tax_requested_by',
                'tax_completed_at',
                'tax_completed_by',
            ]);
        });
    }
};
