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
            $table->boolean('is_billable')->default(false)->after('amount');
            $table->decimal('billable_amount', 15, 2)->nullable()->after('is_billable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leg_additional_costs', function (Blueprint $table) {
            $table->dropColumn(['is_billable', 'billable_amount']);
        });
    }
};
