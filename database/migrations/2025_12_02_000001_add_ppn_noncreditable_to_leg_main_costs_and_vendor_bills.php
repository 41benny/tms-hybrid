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
            $table->boolean('ppn_noncreditable')->default(false)->after('ppn');
        });

        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->boolean('ppn_noncreditable')->default(false)->after('ppn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn('ppn_noncreditable');
        });

        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->dropColumn('ppn_noncreditable');
        });
    }
};
