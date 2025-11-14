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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('shipment_leg_id')->nullable()->after('transport_id')->constrained('shipment_legs')->nullOnDelete();
            $table->index('shipment_leg_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['shipment_leg_id']);
            $table->dropIndex(['shipment_leg_id']);
            $table->dropColumn('shipment_leg_id');
        });
    }
};
