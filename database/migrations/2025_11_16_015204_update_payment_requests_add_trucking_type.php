<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update payment_type enum to include 'trucking'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE payment_requests MODIFY COLUMN payment_type ENUM('vendor_bill', 'manual', 'trucking') NOT NULL DEFAULT 'vendor_bill'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE payment_requests MODIFY COLUMN payment_type ENUM('vendor_bill', 'manual') NOT NULL DEFAULT 'vendor_bill'");
        }
    }
};
