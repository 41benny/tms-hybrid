<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // === Super Admin ===
        $email = config('app.super_admin_email', 'superadmin@tms.local');

        $superAdmin = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($superAdmin->role !== User::ROLE_SUPER_ADMIN) {
            $superAdmin->forceFill([
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
                'deactivated_at' => null,
            ])->save();
        }

        // Super admin gets all menus
        $superAdmin->menus()->sync(Menu::query()->pluck('id')->all());

        // === Finance User (Contoh) ===
        // Finance bisa akses dashboard hutang + pengajuan pembayaran
        $finance = User::query()->firstOrCreate(
            ['email' => 'finance@tms.local'],
            [
                'name' => 'Finance User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $financeMenus = Menu::query()
            ->whereIn('slug', [
                'dashboard',
                'finance.dashboard',
                'hutang',              // ← Finance bisa akses dashboard hutang
                'payment-requests',
                'invoices',
                'cash-banks',
                'accounting.journals',
                'accounting.coa',
            ])
            ->pluck('id')
            ->all();

        $finance->menus()->sync($financeMenus);

        // === Sales User (Contoh) ===
        // Sales bisa ajukan pembayaran, tapi TIDAK bisa akses dashboard hutang
        $sales = User::query()->firstOrCreate(
            ['email' => 'sales@tms.local'],
            [
                'name' => 'Sales User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SALES,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $salesMenus = Menu::query()
            ->whereIn('slug', [
                'dashboard',
                'customers',
                'job-orders',
                'payment-requests', // ← Sales bisa ajukan pembayaran
                'invoices',         // Sales bisa lihat invoices
            ])
            ->pluck('id')
            ->all();

        $sales->menus()->sync($salesMenus);
    }
}
