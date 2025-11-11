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
        // Main Costs (per leg)
        Schema::create('leg_main_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_leg_id')->constrained('shipment_legs')->cascadeOnDelete();
            $table->decimal('vendor_cost', 15, 2)->default(0); // Total cost from vendor
            $table->decimal('uang_jalan', 15, 2)->default(0); // Road money
            $table->decimal('bbm', 15, 2)->default(0); // Fuel
            $table->decimal('toll', 15, 2)->default(0); // Toll fees
            $table->decimal('other_costs', 15, 2)->default(0); // Other misc costs
            $table->timestamps();

            $table->index('shipment_leg_id');
        });

        // Additional Costs (dynamic per leg)
        Schema::create('leg_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_leg_id')->constrained('shipment_legs')->cascadeOnDelete();
            $table->string('cost_type'); // handling, loading, unloading, storage, etc
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->timestamps();

            $table->index('shipment_leg_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leg_additional_costs');
        Schema::dropIfExists('leg_main_costs');
    }
};
