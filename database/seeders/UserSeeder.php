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
        $email = config('app.super_admin_email', 'superadmin@tms.local');

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($user->role !== User::ROLE_SUPER_ADMIN) {
            $user->forceFill([
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
                'deactivated_at' => null,
            ])->save();
        }

        $user->menus()->sync(Menu::query()->pluck('id')->all());
    }
}
