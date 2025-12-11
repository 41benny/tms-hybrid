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
        Schema::table('journals', function (Blueprint $table) {
            $table->boolean('is_revision')->default(false)->after('memo');
            $table->unsignedBigInteger('original_journal_id')->nullable()->after('is_revision');
            $table->timestamp('revised_at')->nullable()->after('original_journal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn(['is_revision', 'original_journal_id', 'revised_at']);
        });
    }
};
