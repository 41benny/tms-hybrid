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
        Schema::table('invoices', function (Blueprint $table) {
            // Add approval fields at the end of the table
            $table->enum('approval_status', ['draft', 'pending_approval', 'approved', 'rejected'])
                  ->default('draft')
                  ->after('updated_at');
            $table->foreignId('approved_by')
                  ->nullable()
                  ->after('approval_status')
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('approved_at')
                  ->nullable()
                  ->after('approved_by');
            $table->foreignId('rejected_by')
                  ->nullable()
                  ->after('approved_at')
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('rejected_at')
                  ->nullable()
                  ->after('rejected_by');
            $table->text('rejection_reason')
                  ->nullable()
                  ->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
                'rejection_reason'
            ]);
        });
    }
};
