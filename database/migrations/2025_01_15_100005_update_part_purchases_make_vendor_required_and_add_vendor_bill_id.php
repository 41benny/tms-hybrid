<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_purchases', function (Blueprint $table) {
            // Ubah vendor_id menjadi required (NOT NULL)
            $table->foreignId('vendor_id')->nullable(false)->change();

            // Tambah vendor_bill_id untuk relasi ke VendorBill
            $table->foreignId('vendor_bill_id')->nullable()->after('vendor_id')->constrained('vendor_bills')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('part_purchases', function (Blueprint $table) {
            $table->dropForeign(['vendor_bill_id']);
            $table->dropColumn('vendor_bill_id');

            // Kembalikan vendor_id menjadi nullable
            $table->foreignId('vendor_id')->nullable()->change();
        });
    }
};
