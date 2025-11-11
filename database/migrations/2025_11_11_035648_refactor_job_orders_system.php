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
        // Drop foreign key dari transports terlebih dahulu
        Schema::table('transports', function (Blueprint $table) {
            $table->dropForeign(['job_order_item_id']);
            $table->dropColumn('job_order_item_id');
        });

        // Drop old job_order_items table
        Schema::dropIfExists('job_order_items');

        // Update job_orders table
        Schema::table('job_orders', function (Blueprint $table) {
            // Add sales_id
            $table->foreignId('sales_id')->nullable()->after('customer_id')->constrained('sales')->nullOnDelete();

            // Update service_type enum
            $table->dropColumn('service_type');
        });

        Schema::table('job_orders', function (Blueprint $table) {
            $table->enum('service_type', ['multimoda', 'inland'])->after('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropForeign(['sales_id']);
            $table->dropColumn('sales_id');
            $table->dropColumn('service_type');
        });

        Schema::table('job_orders', function (Blueprint $table) {
            $table->string('service_type')->after('order_date');
        });

        // Recreate job_order_items table
        Schema::create('job_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete();
            $table->string('equipment_name')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('qty', 10, 2)->default(1);
            $table->foreignId('origin_route_id')->nullable()->constrained('routes')->nullOnDelete();
            $table->foreignId('destination_route_id')->nullable()->constrained('routes')->nullOnDelete();
            $table->string('origin_text')->nullable();
            $table->string('destination_text')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
        });

        // Restore foreign key di transports
        Schema::table('transports', function (Blueprint $table) {
            $table->foreignId('job_order_item_id')->nullable()->after('job_order_id')->constrained('job_order_items')->nullOnDelete();
        });
    }
};
