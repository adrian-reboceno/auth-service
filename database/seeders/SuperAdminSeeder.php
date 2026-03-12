<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $superAdminRole = DB::table('roles')->where('name', 'super_admin')->first();
        if (!$superAdminRole) {
            return;
        }

        $userId = (string) Str::uuid();

        DB::table('users')->insertOrIgnore([
            'id' => $userId,
            'full_name' => 'System Administrator',
            'email' => 'admin@pharmacy.local', // Placeholder email
            'password_hash' => Hash::make('Admin123!', ['rounds' => 12]),
            'branch_id' => 1,
            'locale' => 'en',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_roles')->insertOrIgnore([
            'user_id' => $userId,
            'role_id' => $superAdminRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
