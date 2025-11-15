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
        Schema::create('part_usages', function (Blueprint $table) {
            $table->id();
            $table->string('usage_number')->unique();
            $table->date('usage_date');
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 15, 2)->default(0); // harga per unit saat keluar
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->foreignId('truck_id')->nullable()->constrained('trucks')->nullOnDelete(); // jika untuk truck tertentu
            $table->string('usage_type')->default('maintenance'); // maintenance, repair, replacement, dll
            $table->text('description')->nullable();
            $table->foreignId('part_purchase_id')->nullable()->constrained('part_purchases')->nullOnDelete(); // jika langsung dari pembelian
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('usage_date');
            $table->index('part_id');
            $table->index('truck_id');
            $table->index('usage_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_usages');
    }
};
