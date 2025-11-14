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
        Schema::table('leg_additional_costs', function (Blueprint $table) {
            $table->string('pic_name')->nullable()->after('vendor_id');
            $table->string('pic_phone', 50)->nullable()->after('pic_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leg_additional_costs', function (Blueprint $table) {
            $table->dropColumn(['pic_name', 'pic_phone']);
        });
    }
};
