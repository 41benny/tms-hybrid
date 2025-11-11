<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update enum to replace air_freight with self_service
        DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran', 'self_service')");

        // Add new fields to leg_main_costs for self_service
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->string('payee_name')->nullable()->after('other_costs');
            $table->string('service_description')->nullable()->after('payee_name');
            $table->decimal('service_amount', 15, 2)->default(0)->after('service_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back to original with air_freight
        DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran', 'air_freight')");

        // Drop the added columns
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn(['payee_name', 'service_description', 'service_amount']);
        });
    }
};
