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
            // Revision tracking fields
            $table->integer('revision_number')->default(0)->after('updated_at');
            $table->foreignId('original_invoice_id')->nullable()->after('revision_number')->constrained('invoices')->nullOnDelete();
            $table->timestamp('revised_at')->nullable()->after('original_invoice_id');
            $table->foreignId('revised_by')->nullable()->after('revised_at')->constrained('users')->nullOnDelete();
            $table->text('revision_reason')->nullable()->after('revised_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['original_invoice_id']);
            $table->dropForeign(['revised_by']);
            $table->dropColumn([
                'revision_number',
                'original_invoice_id',
                'revised_at',
                'revised_by',
                'revision_reason',
            ]);
        });
    }
};
