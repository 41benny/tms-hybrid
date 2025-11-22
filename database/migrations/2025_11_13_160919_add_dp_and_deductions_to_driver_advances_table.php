<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_advances', function (Blueprint $table) {
            // DP fields
            $table->decimal('dp_amount', 15, 2)->default(0)->after('amount');
            $table->date('dp_paid_date')->nullable()->after('dp_amount');

            // Deduction fields (potongan saat pelunasan)
            $table->decimal('deduction_savings', 15, 2)->default(0)->after('paid_date');
            $table->decimal('deduction_guarantee', 15, 2)->default(0)->after('deduction_savings');

            // Settlement fields (pelunasan)
            $table->date('settlement_date')->nullable()->after('deduction_guarantee');
            $table->text('settlement_notes')->nullable()->after('settlement_date');
        });

        // Update existing status enum to include 'dp_paid'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE driver_advances MODIFY COLUMN status ENUM('pending', 'dp_paid', 'settled') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_advances', function (Blueprint $table) {
            $table->dropColumn([
                'dp_amount',
                'dp_paid_date',
                'deduction_savings',
                'deduction_guarantee',
                'settlement_date',
                'settlement_notes',
            ]);
        });

        // Revert status enum
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE driver_advances MODIFY COLUMN status ENUM('pending', 'paid', 'settled') DEFAULT 'pending'");
        }
    }
};
