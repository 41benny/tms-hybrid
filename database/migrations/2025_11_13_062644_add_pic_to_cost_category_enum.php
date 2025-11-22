<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran', 'asuransi', 'pic') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE shipment_legs MODIFY COLUMN cost_category ENUM('trucking', 'vendor', 'pelayaran', 'asuransi') NOT NULL");
        }
    }
};
