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
        Schema::table('cash_bank_accounts', function (Blueprint $table) {
            // Type: cash atau bank
            $table->enum('type', ['cash', 'bank'])->default('cash')->after('code');
            
            // Bank Information (untuk type='bank')
            $table->string('account_number')->nullable()->after('type');
            $table->string('bank_name')->nullable()->after('account_number');
            $table->string('branch')->nullable()->after('bank_name');
            $table->string('account_holder')->nullable()->after('branch');
            
            // Link ke Chart of Account
            $table->foreignId('coa_id')
                ->nullable()
                ->after('account_holder')
                ->constrained('chart_of_accounts')
                ->onDelete('set null');
            
            // Balance tracking
            $table->decimal('opening_balance', 15, 2)->default(0)->after('coa_id');
            $table->decimal('current_balance', 15, 2)->default(0)->after('opening_balance');
            
            // Description
            $table->text('description')->nullable()->after('current_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->dropColumn([
                'type',
                'account_number',
                'bank_name',
                'branch',
                'account_holder',
                'coa_id',
                'opening_balance',
                'current_balance',
                'description'
            ]);
        });
    }
};
