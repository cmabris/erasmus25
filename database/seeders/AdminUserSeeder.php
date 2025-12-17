<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
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
        $superAdminRole = Role::where('name', Roles::SUPER_ADMIN)->first();
        if ($superAdminRole && ! $admin->hasRole(Roles::SUPER_ADMIN)) {
            $admin->assignRole(Roles::SUPER_ADMIN);
        }
    }
}
