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
        Schema::create('part_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->string('location')->default('main'); // lokasi gudang (main, branch1, dll)
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0); // harga rata-rata per unit
            $table->timestamps();

            $table->unique(['part_id', 'location']);
            $table->index('part_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_stocks');
    }
};
