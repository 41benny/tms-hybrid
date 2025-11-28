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
        Schema::table('job_orders', function (Blueprint $table) {
            // Add composite index for invoice modal queries
            // This speeds up: WHERE customer_id = ? AND status = ?
            $table->index(['customer_id', 'status', 'created_at'], 'idx_job_orders_invoice_modal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropIndex('idx_job_orders_invoice_modal');
        });
    }
};
