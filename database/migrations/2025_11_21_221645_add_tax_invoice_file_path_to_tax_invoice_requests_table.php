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
        Schema::table('tax_invoice_requests', function (Blueprint $table) {
            $table->string('tax_invoice_file_path')->nullable()->after('tax_invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_invoice_requests', function (Blueprint $table) {
            $table->dropColumn('tax_invoice_file_path');
        });
    }
};
