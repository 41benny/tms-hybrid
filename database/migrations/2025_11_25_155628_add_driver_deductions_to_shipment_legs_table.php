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
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->decimal('driver_savings_deduction', 15, 2)->default(0)->after('uang_jalan')->comment('Potongan tabungan supir');
            $table->decimal('driver_guarantee_deduction', 15, 2)->default(0)->after('driver_savings_deduction')->comment('Potongan jaminan supir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn(['driver_savings_deduction', 'driver_guarantee_deduction']);
        });
    }
};
