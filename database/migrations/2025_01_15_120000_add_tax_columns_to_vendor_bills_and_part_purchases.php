<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update vendor_bills (jika tabel sudah ada; pada urutan timestamp saat ini mungkin belum dibuat)
        if (Schema::hasTable('vendor_bills')) {
            Schema::table('vendor_bills', function (Blueprint $table) {
                if (! Schema::hasColumn('vendor_bills', 'dpp')) {
                    $table->decimal('dpp', 15, 2)->default(0)->after('total_amount')->comment('Dasar Pengenaan Pajak');
                    $table->decimal('ppn', 15, 2)->default(0)->after('dpp')->comment('PPN 11%');
                    $table->decimal('pph23', 15, 2)->default(0)->after('ppn')->comment('PPh 23 (dipotong)');
                }
            });
        }

        // Update part_purchases (tabel ini sudah ada di batch migrasi January)
        if (Schema::hasTable('part_purchases')) {
            Schema::table('part_purchases', function (Blueprint $table) {
                if (! Schema::hasColumn('part_purchases', 'dpp')) {
                    $table->decimal('dpp', 15, 2)->default(0)->after('total_amount')->comment('Dasar Pengenaan Pajak');
                    $table->decimal('ppn', 15, 2)->default(0)->after('dpp')->comment('PPN 11%');
                    $table->decimal('pph23', 15, 2)->default(0)->after('ppn')->comment('PPh 23 (dipotong)');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vendor_bills')) {
            Schema::table('vendor_bills', function (Blueprint $table) {
                if (Schema::hasColumn('vendor_bills', 'dpp')) {
                    $table->dropColumn(['dpp', 'ppn', 'pph23']);
                }
            });
        }
        if (Schema::hasTable('part_purchases')) {
            Schema::table('part_purchases', function (Blueprint $table) {
                if (Schema::hasColumn('part_purchases', 'dpp')) {
                    $table->dropColumn(['dpp', 'ppn', 'pph23']);
                }
            });
        }
    }
};
