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
        // Remove self_service from enum (back to trucking, vendor, pelayaran only)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran')");
        }

        // Remove self_service fields from leg_main_costs
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn(['payee_name', 'service_description', 'service_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add self_service back to enum
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran', 'self_service')");
        }

        // Add back the columns
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->string('payee_name')->nullable()->after('other_costs');
            $table->string('service_description')->nullable()->after('payee_name');
            $table->decimal('service_amount', 15, 2)->default(0)->after('service_description');
        });
    }
};
