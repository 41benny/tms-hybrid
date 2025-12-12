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
        Schema::table('journal_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_lines', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->after('vendor_id')->constrained('drivers')->nullOnDelete();
                $table->index('driver_id');
            }
        });

        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_bank_transactions', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->after('vendor_id')->constrained('drivers')->nullOnDelete();
                $table->index('driver_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_lines', function (Blueprint $table) {
            if (Schema::hasColumn('journal_lines', 'driver_id')) {
                $table->dropForeign(['driver_id']);
                $table->dropColumn('driver_id');
            }
        });

        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_bank_transactions', 'driver_id')) {
                $table->dropForeign(['driver_id']);
                $table->dropColumn('driver_id');
            }
        });
    }
};
