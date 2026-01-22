<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\User;
use Database\Seeders\ProductionAdminUserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class SetupProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:production
                            {--force : Ejecutar sin confirmación y saltar solo advertencias (no errores críticos)}
                            {--admin-email= : Email para el super-admin (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preparar la aplicación para producción (migraciones, solo seeders esenciales)';

    /**
     * Email del super-admin capturado del seeder.
     */
    private ?string $adminEmail = null;

    /**
     * Contraseña del super-admin capturada del seeder.
     */
    private ?string $adminPassword = null;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Preparando aplicación para producción...');
        $this->newLine();

        $startTime = microtime(true);

        try {
            // 1. Validaciones de entorno
            if (! $this->validateEnvironment()) {
                return Command::FAILURE;
            }

            // 2. Confirmación de acción
            if (! $this->confirmAction()) {
                $this->warn('Operación cancelada.');

                return Command::FAILURE;
            }

            // 3. Ejecutar migraciones
            if (! $this->runMigrations()) {
                return Command::FAILURE;
            }

            // 4. Ejecutar solo seeders esenciales
            $this->runEssentialSeeders();

            // 5. Limpiar y optimizar cachés
            $this->clearAndOptimizeCaches();

            // 6. Crear storage link
            $this->createStorageLink();

            // 7. Verificaciones post-setup
            $this->verifyPostSetup();

            // 8. Información final
            $this->showFinalInformation($startTime);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error durante la ejecución: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Validar entorno de producción.
     */
    private function validateEnvironment(): bool
    {
        $this->info('🔍 Validando entorno...');

        $criticalErrors = [];
        $warnings = [];

        // Errores críticos (bloquean)
        // 1. Verificar conexión a base de datos
        try {
            DB::connection()->getPdo();
            $this->line('   ✅ Conexión a base de datos... OK');
        } catch (\Exception $e) {
            $criticalErrors[] = 'Conexión a base de datos fallida: '.$e->getMessage();
            $this->line('   ❌ Conexión a base de datos... FALLO');
        }

        // 2. Verificar archivo .env
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $this->line('   ✅ Archivo .env... OK');
        } else {
            $criticalErrors[] = 'El archivo .env no existe en '.$envPath;
            $this->line('   ❌ Archivo .env... NO EXISTE');
        }

        // 3. Verificar permisos de escritura
        $storagePath = storage_path();
        $cachePath = base_path('bootstrap/cache');

        $storageWritable = is_writable($storagePath);
        $cacheWritable = is_writable($cachePath);

        if ($storageWritable && $cacheWritable) {
            $this->line('   ✅ Permisos de escritura... OK');
        } else {
            $issues = [];
            if (! $storageWritable) {
                $issues[] = 'storage/ no tiene permisos de escritura';
            }
            if (! $cacheWritable) {
                $issues[] = 'bootstrap/cache/ no tiene permisos de escritura';
            }
            $criticalErrors[] = implode(', ', $issues);
            $this->line('   ❌ Permisos de escritura... FALLO');
        }

        // Advertencias (no bloquean)
        // 1. Verificar APP_ENV
        $appEnv = config('app.env');
        if ($appEnv === 'production') {
            $this->line('   ✅ APP_ENV... production');
        } else {
            $warnings[] = "APP_ENV no está en 'production' (actual: {$appEnv})";
            $this->line("   ⚠️  APP_ENV... {$appEnv} (debería ser 'production')");
        }

        // 2. Verificar APP_DEBUG
        $appDebug = config('app.debug');
        if (! $appDebug) {
            $this->line('   ✅ APP_DEBUG... false');
        } else {
            $warnings[] = "APP_DEBUG está en 'true' (debería ser 'false' en producción)";
            $this->line('   ⚠️  APP_DEBUG... true (debería ser false)');
        }

        $this->newLine();

        // Manejar errores críticos
        if (! empty($criticalErrors)) {
            $this->error('❌ Errores críticos detectados:');
            foreach ($criticalErrors as $error) {
                $this->error("   • {$error}");
            }
            $this->newLine();
            $this->error('No se puede continuar. Corrige estos errores antes de ejecutar el setup.');

            return false;
        }

        // Manejar advertencias
        if (! empty($warnings) && ! $this->option('force')) {
            $this->warn('⚠️  Se detectaron problemas en la configuración del entorno:');
            foreach ($warnings as $warning) {
                $this->warn("   • {$warning}");
            }
            $this->newLine();

            if (! $this->confirm('¿Deseas continuar de todas formas?', false)) {
                return false;
            }
        } elseif (! empty($warnings) && $this->option('force')) {
            $this->comment('⚠️  Advertencias detectadas pero omitidas (--force):');
            foreach ($warnings as $warning) {
                $this->comment("   • {$warning}");
            }
            $this->newLine();
        }

        return true;
    }

    /**
     * Confirmar acción destructiva.
     */
    private function confirmAction(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $this->warn('⚠️  ADVERTENCIA: Esto ejecutará migraciones y seeders esenciales.');
        $this->newLine();
        $this->line('Seeders que se ejecutarán:');
        $essentialSeeders = [
            'LanguagesSeeder',
            'ProgramsSeeder',
            'AcademicYearsSeeder',
            'DocumentCategoriesSeeder',
            'SettingsSeeder',
            'RolesAndPermissionsSeeder',
            'NewsTagSeeder',
            'ProductionAdminUserSeeder',
        ];
        foreach ($essentialSeeders as $seeder) {
            $this->line("   • {$seeder}");
        }
        $this->newLine();

        return $this->confirm('¿Deseas continuar?', false);
    }

    /**
     * Ejecutar migraciones fresh con doble confirmación.
     */
    private function runMigrations(): bool
    {
        $this->info('📦 Ejecutando migraciones...');
        $this->warn('⚠️  ADVERTENCIA: migrate:fresh eliminará TODOS los datos existentes.');
        $this->newLine();

        if (! $this->option('force')) {
            if (! $this->confirm('¿Estás seguro de que deseas ejecutar migrate:fresh?', false)) {
                $this->warn('Operación cancelada. No se ejecutarán las migraciones.');

                return false;
            }
        }

        $this->line('   → migrate:fresh (elimina y recrea tablas)');
        Artisan::call('migrate:fresh', [], $this->getOutput());

        $this->info('✅ Migraciones ejecutadas correctamente');
        $this->newLine();

        return true;
    }

    /**
     * Ejecutar solo seeders esenciales.
     */
    private function runEssentialSeeders(): void
    {
        $this->info('🌱 Ejecutando seeders esenciales...');

        $seeders = [
            'LanguagesSeeder',
            'ProgramsSeeder',
            'AcademicYearsSeeder',
            'DocumentCategoriesSeeder',
            'SettingsSeeder',
            'RolesAndPermissionsSeeder',
            'NewsTagSeeder',
        ];

        foreach ($seeders as $seeder) {
            $this->line("   → {$seeder}...");
            Artisan::call('db:seed', [
                '--class' => $seeder,
            ], $this->getOutput());
        }

        // ProductionAdminUserSeeder requiere manejo especial para capturar email y contraseña
        $this->line('   → ProductionAdminUserSeeder...');
        $seeder = new ProductionAdminUserSeeder;
        $seeder->setCommand($this);

        // Establecer email si se proporcionó como opción
        if ($this->option('admin-email')) {
            $seeder->email = $this->option('admin-email');
        }

        // Ejecutar seeder
        $seeder->run();

        // Capturar credenciales desde las propiedades del seeder
        if ($seeder->email) {
            $this->adminEmail = $seeder->email;
        }
        if ($seeder->password) {
            $this->adminPassword = $seeder->password;
        }

        $this->info('✅ Seeders esenciales ejecutados');
        $this->newLine();
    }

    /**
     * Limpiar y optimizar cachés.
     */
    private function clearAndOptimizeCaches(): void
    {
        $this->info('🧹 Limpiando y optimizando cachés...');

        // Limpiar cachés
        $clearCommands = [
            'config:clear' => 'Configuración',
            'cache:clear' => 'Caché de aplicación',
            'route:clear' => 'Rutas',
            'view:clear' => 'Vistas',
        ];

        foreach ($clearCommands as $command => $description) {
            $this->line("   → Limpiando {$description}...");
            Artisan::call($command);
        }

        // Limpiar caché de permisos
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            $this->line('   → Limpiando permisos (Spatie Permission)...');
            Artisan::call('permission:cache-reset');
        }

        // Optimizar cachés
        $optimizeCommands = [
            'config:cache' => 'Configuración',
            'route:cache' => 'Rutas',
            'view:cache' => 'Vistas',
        ];

        foreach ($optimizeCommands as $command => $description) {
            $this->line("   → Optimizando {$description}...");
            Artisan::call($command);
        }

        // Event cache (si existe)
        try {
            Artisan::call('event:cache');
            $this->line('   → Optimizando eventos...');
        } catch (\Exception $e) {
            // Comando puede no existir en todas las versiones
        }

        $this->info('✅ Cachés optimizados');
        $this->newLine();
    }

    /**
     * Crear enlace simbólico de storage.
     */
    private function createStorageLink(): void
    {
        $this->info('🔗 Creando enlace de storage...');

        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (File::exists($linkPath)) {
            if (is_link($linkPath)) {
                $this->line('   → El enlace ya existe');
            } else {
                $this->warn('   → Existe un archivo/directorio en public/storage, no se puede crear el enlace');
            }
        } else {
            try {
                Artisan::call('storage:link');
                $this->line('   → Enlace creado');
            } catch (\Exception $e) {
                $this->warn("   → No se pudo crear el enlace: {$e->getMessage()}");
            }
        }

        $this->info('✅ Enlace de storage verificado');
        $this->newLine();
    }

    /**
     * Verificar post-setup.
     */
    private function verifyPostSetup(): void
    {
        $this->info('✅ Verificaciones post-setup...');

        $issues = [];

        // Verificar usuario super-admin
        $superAdminCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'super-admin');
        })->count();

        if ($superAdminCount > 0) {
            $this->line('   ✅ Usuario super-admin verificado');
        } else {
            $issues[] = 'No se encontró ningún usuario super-admin';
            $this->line('   ❌ Usuario super-admin... NO ENCONTRADO');
        }

        // Verificar roles
        $requiredRoles = ['super-admin', 'admin', 'editor', 'viewer'];
        $existingRoles = Role::whereIn('name', $requiredRoles)->pluck('name')->toArray();
        $missingRoles = array_diff($requiredRoles, $existingRoles);

        if (empty($missingRoles)) {
            $this->line('   ✅ Roles y permisos verificados');
        } else {
            $issues[] = 'Faltan roles: '.implode(', ', $missingRoles);
            $this->line('   ❌ Roles... FALTANTES: '.implode(', ', $missingRoles));
        }

        // Verificar idiomas
        $languagesCount = Language::where('is_active', true)->count();
        if ($languagesCount > 0) {
            $this->line('   ✅ Idiomas configurados');
        } else {
            $issues[] = 'No se encontraron idiomas activos';
            $this->line('   ❌ Idiomas... NO CONFIGURADOS');
        }

        if (! empty($issues)) {
            $this->newLine();
            $this->warn('⚠️  Se detectaron problemas:');
            foreach ($issues as $issue) {
                $this->warn("   • {$issue}");
            }
        }

        $this->newLine();
    }

    /**
     * Mostrar información final.
     */
    private function showFinalInformation(float $startTime): void
    {
        $executionTime = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('✅ Aplicación lista para producción');
        $this->newLine();

        if ($this->adminEmail && $this->adminPassword) {
            $this->line('📋 Información importante:');
            $this->line("   Super Admin: {$this->adminEmail}");
            $this->line("   🔐 Contraseña temporal: {$this->adminPassword}");
            $this->newLine();
            $this->warn('⚠️  IMPORTANTE:');
            $this->line('   - Esta contraseña solo se mostrará una vez');
            $this->line('   - Usa "Olvidé mi contraseña" en el primer acceso para establecer una nueva');
            $this->line('   - No compartas esta contraseña');
            $this->newLine();
        } else {
            $this->warn('⚠️  No se pudieron capturar las credenciales del super-admin.');
            $this->line('   Revisa los logs o ejecuta ProductionAdminUserSeeder manualmente.');
            $this->newLine();
        }

        $this->line('💡 Comandos útiles para producción:');
        $this->line('   • php artisan config:cache     - Optimizar configuración');
        $this->line('   • php artisan route:cache       - Optimizar rutas');
        $this->line('   • php artisan view:cache        - Optimizar vistas');
        $this->line('   • php artisan queue:work        - Procesar colas');
        $this->line('   • php artisan schedule:run      - Ejecutar tareas programadas');
        $this->newLine();

        $this->comment("⏱️  Tiempo de ejecución: {$executionTime} segundos");
    }
}
