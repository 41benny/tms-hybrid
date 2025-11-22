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
        Schema::create('tax_invoice_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            
            // Invoice data snapshot
            $table->string('transaction_type', 2); // 04, 05, 08
            $table->string('customer_name');
            $table->string('customer_npwp')->nullable();
            $table->decimal('dpp', 15, 2);
            $table->decimal('ppn', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->text('description')->nullable();
            
            // Request tracking
            $table->enum('status', ['requested', 'completed'])->default('requested');
            $table->foreignId('requested_by')->constrained('users');
            $table->timestamp('requested_at');
            
            // Tax invoice data
            $table->string('tax_invoice_number')->nullable()->unique();
            $table->date('tax_invoice_date')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('requested_at');
            $table->index('tax_invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_invoice_requests');
    }
};
