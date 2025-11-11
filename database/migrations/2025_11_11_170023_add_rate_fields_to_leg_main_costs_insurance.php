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
            $table->decimal('premium_rate', 5, 2)->default(0)->after('insured_value')->comment('Rate premi dalam %');
            $table->decimal('admin_fee', 15, 2)->default(0)->after('premium_cost')->comment('Biaya admin polis');
            $table->decimal('billable_rate', 5, 2)->default(0)->after('admin_fee')->comment('Rate untuk customer dalam %');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn(['premium_rate', 'admin_fee', 'billable_rate']);
        });
    }
};
