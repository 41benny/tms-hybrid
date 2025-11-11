<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leg_main_costs', function (Blueprint $table) {
            // Tax fields (for vendor)
            $table->decimal('ppn', 15, 2)->default(0)->after('vendor_cost');
            $table->decimal('pph23', 15, 2)->default(0)->after('ppn');

            // Sea freight specific fields
            $table->string('shipping_line')->nullable()->after('other_costs');
            $table->decimal('freight_cost', 15, 2)->default(0)->after('shipping_line');
            $table->string('container_no')->nullable()->after('freight_cost');
        });
    }

    public function down(): void
    {
        Schema::table('leg_main_costs', function (Blueprint $table) {
            $table->dropColumn(['ppn', 'pph23', 'shipping_line', 'freight_cost', 'container_no']);
        });
    }
};
