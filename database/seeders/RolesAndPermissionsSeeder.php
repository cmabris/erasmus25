<?php

namespace Database\Seeders;

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

        // Crear permisos para Programas
        Permission::firstOrCreate(['name' => 'programs.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'programs.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'programs.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'programs.delete', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'programs.*', 'guard_name' => 'web']);

        // Crear permisos para Convocatorias
        Permission::firstOrCreate(['name' => 'calls.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'calls.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'calls.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'calls.delete', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'calls.publish', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'calls.*', 'guard_name' => 'web']);

        // Crear permisos para Noticias
        Permission::firstOrCreate(['name' => 'news.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'news.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'news.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'news.delete', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'news.publish', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'news.*', 'guard_name' => 'web']);

        // Crear permisos para Documentos
        Permission::firstOrCreate(['name' => 'documents.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'documents.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'documents.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'documents.delete', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'documents.*', 'guard_name' => 'web']);

        // Crear permisos para Eventos
        Permission::firstOrCreate(['name' => 'events.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'events.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'events.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'events.delete', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'events.*', 'guard_name' => 'web']);

        // Crear permisos para Usuarios
        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.delete', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.*', 'guard_name' => 'web']);

        // Crear roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Asignar todos los permisos a super-admin
        $superAdmin->givePermissionTo(Permission::all());

        // Asignar permisos a admin (todos excepto gestión de usuarios)
        $admin->givePermissionTo([
            'programs.*',
            'calls.*',
            'news.*',
            'documents.*',
            'events.*',
        ]);

        // Asignar permisos a editor (crear y editar, pero no eliminar ni publicar)
        $editor->givePermissionTo([
            'programs.view',
            'programs.create',
            'programs.edit',
            'calls.view',
            'calls.create',
            'calls.edit',
            'news.view',
            'news.create',
            'news.edit',
            'documents.view',
            'documents.create',
            'documents.edit',
            'events.view',
            'events.create',
            'events.edit',
        ]);

        // Asignar permisos a viewer (solo lectura)
        $viewer->givePermissionTo([
            'programs.view',
            'calls.view',
            'news.view',
            'documents.view',
            'events.view',
        ]);
    }
}
