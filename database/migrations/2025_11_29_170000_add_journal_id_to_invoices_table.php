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
            $table->unsignedBigInteger('journal_id')->nullable()->after('total_amount');
            $table->foreign('journal_id')->references('id')->on('journals')->onDelete('set null');
            $table->index('journal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['journal_id']);
            $table->dropIndex(['journal_id']);
            $table->dropColumn('journal_id');
        });
    }
};
