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
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders');
            $table->foreignId('job_order_item_id')->nullable()->constrained('job_order_items')->nullOnDelete();
            $table->string('executor_type'); // internal|vendor
            $table->foreignId('truck_id')->nullable()->constrained('trucks')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->enum('status', ['planned', 'on_route', 'delivered', 'closed', 'cancelled'])->default('planned');
            $table->string('spj_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['job_order_id', 'status']);
            $table->index(['executor_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transports');
    }
};
