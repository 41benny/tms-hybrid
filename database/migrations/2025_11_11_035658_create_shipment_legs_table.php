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
        Schema::create('shipment_legs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->integer('leg_number'); // 1, 2, 3, dst
            $table->string('leg_code')->unique(); // LEG-49839

            // Transport Details
            $table->enum('cost_category', ['trucking', 'vendor', 'pelayaran', 'air_freight']);
            $table->enum('executor_type', ['own_fleet', 'vendor'])->default('vendor');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('truck_id')->nullable()->constrained('trucks')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('vessel_name')->nullable(); // For sea/air freight

            // Schedule & Quantity
            $table->date('load_date');
            $table->date('unload_date');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->text('serial_numbers')->nullable();

            // Status
            $table->enum('status', ['pending', 'in_transit', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['job_order_id', 'leg_number']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_legs');
    }
};
