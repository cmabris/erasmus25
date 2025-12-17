<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'admin@erasmus-murcia.es';

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrador',
                'email' => $adminEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol de super-admin
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole && ! $admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }
    }
}
