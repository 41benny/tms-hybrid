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
        Schema::create('driver_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_leg_id')->constrained('shipment_legs')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->string('advance_number')->unique();
            $table->date('advance_date');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid', 'settled'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index('advance_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_advances');
    }
};
