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
        // Add 'asuransi' to cost_category enum
        DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran', 'asuransi')");

        // Add insurance fields to leg_main_costs
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->string('insurance_provider')->nullable()->after('container_no');
            $table->string('policy_number')->nullable()->after('insurance_provider');
            $table->decimal('insured_value', 15, 2)->default(0)->after('policy_number');
            $table->decimal('premium_cost', 15, 2)->default(0)->after('insured_value');
            $table->decimal('premium_billable', 15, 2)->default(0)->after('premium_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'asuransi' from enum
        DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran')");

        // Drop insurance fields
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn(['insurance_provider', 'policy_number', 'insured_value', 'premium_cost', 'premium_billable']);
        });
    }
};
