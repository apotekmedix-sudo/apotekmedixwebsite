<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'apotekmedix@gmail.com']);

        if (!$admin->exists) {
            $admin = User::firstOrNew(['email' => 'apotekmedix@admin.com']);
        }

        $admin->fill([
            'name' => 'Admin Apotek Medix',
            'email' => 'apotekmedix@gmail.com',
            'password' => Hash::make('Rh.101185'),
            'role' => 'admin',
        ])->save();
    }
}
