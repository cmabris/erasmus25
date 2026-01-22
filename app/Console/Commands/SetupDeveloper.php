<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SetupDeveloper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:developer
                            {--force : Ejecutar sin confirmación}
                            {--no-cache : No limpiar cachés}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preparar la aplicación para desarrollo (migraciones, seeders completos, limpieza de cachés)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Preparando aplicación para desarrollo...');
        $this->newLine();

        // 1. Confirmación de acción destructiva
        if (! $this->confirmDestructiveAction()) {
            $this->warn('Operación cancelada.');

            return Command::FAILURE;
        }

        $startTime = microtime(true);

        try {
            // 2. Ejecutar migraciones
            $this->runMigrations();

            // 3. Ejecutar seeders
            $this->runSeeders();

            // 4. Limpiar cachés (si no se especifica --no-cache)
            if (! $this->option('no-cache')) {
                $this->clearCaches();
            }

            // 5. Crear storage link
            $this->createStorageLink();

            // 6. Información final
            $this->showFinalInformation($startTime);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error durante la ejecución: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Confirmar acción destructiva.
     */
    private function confirmDestructiveAction(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $this->warn('⚠️  ADVERTENCIA: Esto eliminará todos los datos existentes.');
        $this->warn('   - Se ejecutarán migraciones fresh (elimina todas las tablas)');
        $this->warn('   - Se ejecutarán todos los seeders (datos de prueba)');
        $this->newLine();

        return $this->confirm('¿Deseas continuar?', false);
    }

    /**
     * Ejecutar migraciones fresh.
     */
    private function runMigrations(): void
    {
        $this->info('📦 Ejecutando migraciones...');
        $this->line('   → migrate:fresh (elimina y recrea tablas)');

        try {
            // Ejecutar migrate:fresh
            $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);

            if ($exitCode !== 0) {
                throw new \RuntimeException('Las migraciones fallaron con código de salida: '.$exitCode);
            }
        } catch (\Exception $e) {
            $this->error('Error en migraciones: '.$e->getMessage());
            throw $e;
        }

        $this->info('✅ Migraciones ejecutadas correctamente');
        $this->newLine();
    }

    /**
     * Ejecutar todos los seeders.
     */
    private function runSeeders(): void
    {
        $this->info('🌱 Ejecutando seeders...');

        try {
            // Ejecutar db:seed
            $exitCode = Artisan::call('db:seed', [
                '--class' => 'DatabaseSeeder',
            ]);

            if ($exitCode !== 0) {
                throw new \RuntimeException('Los seeders fallaron con código de salida: '.$exitCode);
            }
        } catch (\Exception $e) {
            $this->error('Error en seeders: '.$e->getMessage());
            throw $e;
        }

        $this->info('✅ Todos los seeders ejecutados');
        $this->newLine();
    }

    /**
     * Limpiar cachés.
     */
    private function clearCaches(): void
    {
        $this->info('🧹 Limpiando cachés...');

        $commands = [
            'config:clear' => 'Configuración',
            'cache:clear' => 'Caché de aplicación',
            'route:clear' => 'Rutas',
            'view:clear' => 'Vistas',
        ];

        foreach ($commands as $command => $description) {
            $this->line("   → {$description}...");
            Artisan::call($command);
        }

        // Limpiar caché de permisos (Spatie Permission)
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            $this->line('   → Permisos (Spatie Permission)...');
            Artisan::call('permission:cache-reset');
        }

        $this->info('✅ Cachés limpiados');
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

        // Verificar si el enlace ya existe
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
     * Mostrar información final.
     */
    private function showFinalInformation(float $startTime): void
    {
        $executionTime = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('✅ Aplicación lista para desarrollo');
        $this->newLine();

        $this->line('📋 Credenciales de prueba:');
        $this->table(
            ['Rol', 'Email', 'Contraseña'],
            [
                ['Super Admin', 'super-admin@erasmus-murcia.es', 'password'],
                ['Admin', 'admin@erasmus-murcia.es', 'password'],
                ['Editor', 'editor@erasmus-murcia.es', 'password'],
                ['Viewer', 'viewer@erasmus-murcia.es', 'password'],
            ]
        );

        $this->newLine();
        $this->line('🌐 URL: '.config('app.url', 'http://localhost'));
        $this->newLine();

        $this->line('💡 Comandos útiles para desarrollo:');
        $this->line('   • php artisan serve          - Iniciar servidor de desarrollo');
        $this->line('   • php artisan test           - Ejecutar tests');
        $this->line('   • php artisan test --filter - Ejecutar tests filtrados');
        $this->line('   • php artisan tinker         - Abrir Tinker');
        $this->line('   • npm run dev                - Compilar assets en modo desarrollo');
        $this->newLine();

        $this->comment("⏱️  Tiempo de ejecución: {$executionTime} segundos");
    }
}
