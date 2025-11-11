<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete();
            $table->string('cargo_type')->nullable(); // Nama cargo/equipment
            $table->decimal('quantity', 10, 2)->default(1);
            $table->text('serial_numbers')->nullable(); // SN-001, SN-002
            $table->timestamps();

            $table->index('job_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_items');
    }
};
