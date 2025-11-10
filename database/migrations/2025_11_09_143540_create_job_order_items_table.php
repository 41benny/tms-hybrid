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

            $table->index(['origin_route_id', 'destination_route_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_order_items');
    }
};
