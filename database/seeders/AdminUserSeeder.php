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
        // Super Admin
        $superAdminEmail = 'super-admin@erasmus-murcia.es';
        $superAdmin = User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => 'Super Administrador',
                'email' => $superAdminEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdminRole = Role::where('name', Roles::SUPER_ADMIN)->first();
        if ($superAdminRole && ! $superAdmin->hasRole(Roles::SUPER_ADMIN)) {
            $superAdmin->assignRole(Roles::SUPER_ADMIN);
        }

        // Admin
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
        $adminRole = Role::where('name', Roles::ADMIN)->first();
        if ($adminRole && ! $admin->hasRole(Roles::ADMIN)) {
            $admin->assignRole(Roles::ADMIN);
        }

        // Editor
        $editorEmail = 'editor@erasmus-murcia.es';
        $editor = User::firstOrCreate(
            ['email' => $editorEmail],
            [
                'name' => 'Editor',
                'email' => $editorEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $editorRole = Role::where('name', Roles::EDITOR)->first();
        if ($editorRole && ! $editor->hasRole(Roles::EDITOR)) {
            $editor->assignRole(Roles::EDITOR);
        }

        // Viewer
        $viewerEmail = 'viewer@erasmus-murcia.es';
        $viewer = User::firstOrCreate(
            ['email' => $viewerEmail],
            [
                'name' => 'Visualizador',
                'email' => $viewerEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $viewerRole = Role::where('name', Roles::VIEWER)->first();
        if ($viewerRole && ! $viewer->hasRole(Roles::VIEWER)) {
            $viewer->assignRole(Roles::VIEWER);
        }
    }
}
