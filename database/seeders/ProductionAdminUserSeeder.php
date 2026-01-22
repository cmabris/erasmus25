<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ProductionAdminUserSeeder extends Seeder
{
    /**
     * Email del super-admin a crear.
     * Se puede establecer antes de ejecutar el seeder.
     */
    public ?string $email = null;

    /**
     * Contraseña generada (para acceso desde comandos).
     */
    public ?string $password = null;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solicitar email del super-admin si no se ha establecido
        if (! $this->email) {
            $this->email = $this->command->ask('Introduce el email para el usuario super-admin');
        }

        $email = $this->email;

        // Validar formato de email
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->command->error("El email '{$email}' no es válido.");

            return;
        }

        // Verificar si el usuario ya existe
        if (User::where('email', $email)->exists()) {
            $this->command->warn("El usuario con email '{$email}' ya existe. Se omitirá la creación.");

            return;
        }

        // Generar contraseña aleatoria segura (mínimo 16 caracteres)
        $password = $this->generateSecurePassword();
        $this->password = $password; // Almacenar para acceso externo

        // Crear usuario super-admin
        $superAdmin = User::create([
            'name' => 'Super Administrador',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Asignar rol super-admin
        $superAdminRole = Role::where('name', Roles::SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $superAdmin->assignRole(Roles::SUPER_ADMIN);
        } else {
            $this->command->error('El rol super-admin no existe. Ejecuta primero RolesAndPermissionsSeeder.');

            return;
        }

        // Mostrar credenciales
        $this->command->newLine();
        $this->command->info('✅ Usuario super-admin creado correctamente');
        $this->command->newLine();
        $this->command->line('📋 Credenciales:');
        $this->command->line("   Email: {$email}");
        $this->command->line("   Contraseña: {$password}");
        $this->command->newLine();
        $this->command->warn('⚠️  IMPORTANTE:');
        $this->command->line('   - Esta contraseña solo se mostrará una vez');
        $this->command->line('   - Usa "Olvidé mi contraseña" en el primer acceso para establecer una nueva');
        $this->command->line('   - No compartas esta contraseña');
        $this->command->newLine();
    }

    /**
     * Genera una contraseña aleatoria segura.
     *
     * Mínimo 16 caracteres con mayúsculas, minúsculas, números y símbolos.
     */
    private function generateSecurePassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $all = $uppercase.$lowercase.$numbers.$symbols;
        $password = '';

        // Asegurar al menos un carácter de cada tipo
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        // Completar hasta la longitud deseada
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Mezclar los caracteres
        return str_shuffle($password);
    }
}
