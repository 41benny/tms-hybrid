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
        Schema::create('vendor_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('bank_name'); // Nama Bank (e.g., BCA, Mandiri, BNI)
            $table->string('account_number'); // Nomor Rekening
            $table->string('account_holder_name'); // Nama Pemilik Rekening
            $table->string('branch')->nullable(); // Cabang Bank (optional)
            $table->boolean('is_primary')->default(false); // Rekening utama
            $table->boolean('is_active')->default(true); // Status aktif
            $table->text('notes')->nullable(); // Catatan
            $table->timestamps();

            $table->index(['vendor_id', 'is_primary']);
            $table->index(['vendor_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_bank_accounts');
    }
};
