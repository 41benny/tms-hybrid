<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update account 6130 to Beban Bensin
        DB::table('chart_of_accounts')
            ->where('code', '6130')
            ->update(['name' => 'Beban Bensin']);
        
        // Update account 7230 to Beban Parkir / Tol
        DB::table('chart_of_accounts')
            ->where('code', '7230')
            ->update(['name' => 'Beban Parkir / Tol']);
    }

    public function down()
    {
        // Revert to original names
        DB::table('chart_of_accounts')
            ->where('code', '6130')
            ->update(['name' => 'Beban Administrasi Bank']);
        
        DB::table('chart_of_accounts')
            ->where('code', '7230')
            ->update(['name' => 'Beban Administrasi Bank']);
    }
};
