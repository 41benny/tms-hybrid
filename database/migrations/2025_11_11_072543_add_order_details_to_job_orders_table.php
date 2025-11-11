<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('service_type');
            $table->string('destination')->nullable()->after('origin');
            $table->decimal('invoice_amount', 15, 2)->default(0)->after('destination');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['origin', 'destination', 'invoice_amount']);
        });
    }
};
