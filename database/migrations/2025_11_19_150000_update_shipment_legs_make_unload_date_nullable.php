<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make unload_date nullable so user can fill it later
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE shipment_legs MODIFY unload_date DATE NULL');
        }
    }

    public function down(): void
    {
        // On rollback, set null unload_date to load_date, then make column NOT NULL again
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('UPDATE shipment_legs SET unload_date = load_date WHERE unload_date IS NULL');
            DB::statement('ALTER TABLE shipment_legs MODIFY unload_date DATE NOT NULL');
        }
    }
};

