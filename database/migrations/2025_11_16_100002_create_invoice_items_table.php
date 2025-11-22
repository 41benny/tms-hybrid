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
        if (Schema::hasTable('invoice_items')) {
            return; // already created by earlier migration
        }
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('job_order_id')->nullable()->constrained('job_orders')->onDelete('set null');

            // Item Details
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);

            // Optional References
            $table->foreignId('shipment_leg_id')->nullable()->constrained('shipment_legs')->onDelete('set null');
            $table->enum('item_type', ['shipping', 'detention', 'storage', 'handling', 'other'])->default('shipping');

            $table->timestamps();

            $table->index('invoice_id');
            $table->index('job_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
