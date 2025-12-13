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
        Schema::table('shipment_legs', function (Blueprint $table) {
            $table->foreignId('equipment_id')
                ->nullable()
                ->after('truck_id')
                ->constrained('equipments')
                ->nullOnDelete();
            $table->index('equipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_legs', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
            $table->dropIndex(['equipment_id']);
            $table->dropColumn('equipment_id');
        });
    }
};
