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
            // pic_name, pic_phone, cost_type sudah ada, hanya tambah pic_amount dan pic_notes
            $table->decimal('pic_amount', 15, 2)->nullable()->after('pic_phone');
            $table->text('pic_notes')->nullable()->after('pic_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn(['pic_amount', 'pic_notes']);
        });
    }
};
