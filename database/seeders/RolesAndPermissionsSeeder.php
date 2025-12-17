<?php

namespace Database\Seeders;

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear todos los permisos
        foreach (Permissions::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear roles
        $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

        // Asignar todos los permisos a super-admin
        $superAdmin->givePermissionTo(Permission::all());

        // Asignar permisos a admin (todos excepto gestión de usuarios)
        $admin->givePermissionTo([
            Permissions::PROGRAMS_ALL,
            Permissions::CALLS_ALL,
            Permissions::NEWS_ALL,
            Permissions::DOCUMENTS_ALL,
            Permissions::EVENTS_ALL,
        ]);

        // Asignar permisos a editor (crear y editar, pero no eliminar ni publicar)
        $editor->givePermissionTo([
            Permissions::PROGRAMS_VIEW,
            Permissions::PROGRAMS_CREATE,
            Permissions::PROGRAMS_EDIT,
            Permissions::CALLS_VIEW,
            Permissions::CALLS_CREATE,
            Permissions::CALLS_EDIT,
            Permissions::NEWS_VIEW,
            Permissions::NEWS_CREATE,
            Permissions::NEWS_EDIT,
            Permissions::DOCUMENTS_VIEW,
            Permissions::DOCUMENTS_CREATE,
            Permissions::DOCUMENTS_EDIT,
            Permissions::EVENTS_VIEW,
            Permissions::EVENTS_CREATE,
            Permissions::EVENTS_EDIT,
        ]);

        // Asignar permisos a viewer (solo lectura)
        $viewer->givePermissionTo(Permissions::viewOnly());
    }
}
