<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update vendor_bills
        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->decimal('dpp', 15, 2)->default(0)->after('total_amount')->comment('Dasar Pengenaan Pajak');
            $table->decimal('ppn', 15, 2)->default(0)->after('dpp')->comment('PPN 11%');
            $table->decimal('pph23', 15, 2)->default(0)->after('ppn')->comment('PPh 23 (dipotong)');
        });

        // Update part_purchases
        Schema::table('part_purchases', function (Blueprint $table) {
            $table->decimal('dpp', 15, 2)->default(0)->after('total_amount')->comment('Dasar Pengenaan Pajak');
            $table->decimal('ppn', 15, 2)->default(0)->after('dpp')->comment('PPN 11%');
            $table->decimal('pph23', 15, 2)->default(0)->after('ppn')->comment('PPh 23 (dipotong)');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->dropColumn(['dpp', 'ppn', 'pph23']);
        });

        Schema::table('part_purchases', function (Blueprint $table) {
            $table->dropColumn(['dpp', 'ppn', 'pph23']);
        });
    }
};
