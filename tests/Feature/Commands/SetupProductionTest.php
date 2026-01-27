<?php

use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\artisan;

beforeEach(function () {
    // Saltar en modo paralelo - los tests de comandos no son compatibles con ejecución paralela
    // porque requieren SQLite en archivo persistente y pueden tener conflictos entre procesos
    // Detectamos modo paralelo verificando si TEST_TOKEN está definido (Laravel lo establece automáticamente)
    if (getenv('TEST_TOKEN') !== false || isset($_SERVER['TEST_TOKEN'])) {
        $this->markTestSkipped('Los tests de comandos no se ejecutan en modo paralelo');
    }

    // En CI, cerrar cualquier transacción activa antes de continuar
    // Esto evita conflictos con RefreshDatabase que pueden ocurrir en entornos CI
    if (env('CI', false)) {
        try {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
        } catch (\Exception $e) {
            // Ignorar errores si no hay transacción activa
        }
    }

    // Limpiar archivo de BD existente ANTES de configurar para evitar que RefreshDatabase
    // intente hacer VACUUM en un archivo con datos (lo cual falla en SQLite dentro de transacciones)
    $dbPath = database_path('testing_setup_production.sqlite');
    if (File::exists($dbPath)) {
        try {
            \Illuminate\Support\Facades\DB::disconnect('sqlite');
        } catch (\Exception $e) {
            // Ignorar errores si no hay conexión activa
        }
        File::delete($dbPath);
    }

    // Configurar SQLite en archivo persistente para evitar problemas con Artisan::call()
    // en subcomandos (como migrate:fresh) que abren nuevas conexiones
    // El archivo estará vacío, así que RefreshDatabase no intentará hacer VACUUM
    useSqliteFile('testing_setup_production.sqlite');

    // Limpiar storage link si existe
    $linkPath = public_path('storage');
    if (File::exists($linkPath) && is_link($linkPath)) {
        File::delete($linkPath);
    }
});

afterEach(function () {
    // En CI, cerrar cualquier transacción activa antes de limpiar
    // Esto evita conflictos con RefreshDatabase que pueden ocurrir en entornos CI
    if (env('CI', false)) {
        try {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
        } catch (\Exception $e) {
            // Ignorar errores si no hay transacción activa
        }
    }

    // Limpiar archivo de BD después de cada test para asegurar estado limpio para el siguiente
    // Esto evita que RefreshDatabase intente hacer VACUUM en el siguiente test
    $dbPath = database_path('testing_setup_production.sqlite');
    if (File::exists($dbPath)) {
        try {
            \Illuminate\Support\Facades\DB::disconnect('sqlite');
        } catch (\Exception $e) {
            // Ignorar errores si no hay conexión activa
        }
        @File::delete($dbPath);
    }
});

it('valida entorno de producción', function () {
    artisan('setup:production', ['--force' => true])
        ->expectsOutput('🔍 Validando entorno...')
        ->assertSuccessful();
});

it('bloquea si no hay conexión a base de datos', function () {
    // Simular error de conexión cambiando temporalmente la configuración
    Config::set('database.connections.mysql.database', 'database_inexistente');

    try {
        artisan('setup:production', ['--force' => true])
            ->expectsOutput('❌ Errores críticos detectados:')
            ->assertFailed();
    } finally {
        // Restaurar configuración
        Config::set('database.connections.mysql.database', env('DB_DATABASE', 'erasmus25'));
    }
})->skip('Requiere configuración específica de BD');

it('bloquea si no existe archivo .env', function () {
    $envPath = base_path('.env');
    $envBackup = base_path('.env.backup');

    // Hacer backup y eliminar .env
    if (File::exists($envPath)) {
        File::move($envPath, $envBackup);
    }

    try {
        artisan('setup:production', ['--force' => true])
            ->expectsOutput('❌ Errores críticos detectados:')
            ->expectsOutput('El archivo .env no existe')
            ->assertFailed();
    } finally {
        // Restaurar .env
        if (File::exists($envBackup)) {
            File::move($envBackup, $envPath);
        }
    }
})->skip('Requiere manipulación de archivos del sistema');

it('advierte pero permite continuar si APP_ENV no es production', function () {
    Config::set('app.env', 'local');

    artisan('setup:production', ['--force' => true])
        ->expectsOutput('⚠️  APP_ENV... local (debería ser \'production\')')
        ->assertSuccessful();

    Config::set('app.env', env('APP_ENV', 'local'));
});

it('advierte pero permite continuar si APP_DEBUG es true', function () {
    Config::set('app.debug', true);

    artisan('setup:production', ['--force' => true])
        ->expectsOutput('⚠️  APP_DEBUG... true (debería ser false)')
        ->assertSuccessful();

    Config::set('app.debug', env('APP_DEBUG', false));
});

it('opción --force salta solo advertencias', function () {
    Config::set('app.env', 'local');
    Config::set('app.debug', true);

    artisan('setup:production', ['--force' => true])
        ->expectsOutput('⚠️  Advertencias detectadas pero omitidas (--force):')
        ->assertSuccessful();

    Config::set('app.env', env('APP_ENV', 'local'));
    Config::set('app.debug', env('APP_DEBUG', false));
});

it('ejecuta solo seeders esenciales', function () {
    artisan('setup:production', ['--force' => true, '--admin-email' => 'test@example.com'])
        ->expectsOutput('🌱 Ejecutando seeders esenciales...')
        ->expectsOutput('✅ Seeders esenciales ejecutados')
        ->assertSuccessful();

    // Verificar que se ejecutaron los seeders esenciales
    expect(Language::count())->toBeGreaterThan(0);
    expect(\App\Models\Program::count())->toBeGreaterThan(0);
    expect(\App\Models\AcademicYear::count())->toBeGreaterThan(0);
    expect(\App\Models\DocumentCategory::count())->toBeGreaterThan(0);
    expect(\App\Models\Setting::count())->toBeGreaterThan(0);
    expect(Role::count())->toBeGreaterThan(0);
    expect(\App\Models\NewsTag::count())->toBeGreaterThan(0);
});

it('no ejecuta seeders de desarrollo', function () {
    artisan('setup:production', ['--force' => true, '--admin-email' => 'test@example.com'])
        ->assertSuccessful();

    // Verificar que NO se crearon usuarios de desarrollo (solo el super-admin)
    expect(User::where('email', 'admin@erasmus-murcia.es')->exists())->toBeFalse();
    expect(User::where('email', 'editor@erasmus-murcia.es')->exists())->toBeFalse();
    expect(User::where('email', 'viewer@erasmus-murcia.es')->exists())->toBeFalse();

    // Verificar que NO se crearon datos de desarrollo
    expect(\App\Models\Call::count())->toBe(0);
    expect(\App\Models\NewsPost::count())->toBe(0);
    expect(\App\Models\Document::count())->toBe(0);
});

it('crea super-admin correctamente', function () {
    artisan('setup:production', ['--force' => true, '--admin-email' => 'superadmin@test.com'])
        ->assertSuccessful();

    $user = User::where('email', 'superadmin@test.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('super-admin'))->toBeTrue();
});

it('opción --admin-email funciona', function () {
    $email = 'custom-admin@test.com';

    artisan('setup:production', ['--force' => true, '--admin-email' => $email])
        ->assertSuccessful();

    expect(User::where('email', $email)->exists())->toBeTrue();
});

it('solicita email por terminal si no se proporciona', function () {
    artisan('setup:production', ['--force' => true])
        ->expectsQuestion('Introduce el email para el usuario super-admin', 'test@example.com')
        ->assertSuccessful();

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('genera contraseña aleatoria segura', function () {
    artisan('setup:production', ['--force' => true, '--admin-email' => 'test@example.com'])
        ->assertSuccessful();

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    // La contraseña debe estar hasheada, no en texto plano
    expect($user->password)->not->toBe('password');
    expect(strlen($user->password))->toBeGreaterThan(50); // Hash bcrypt es largo
});

it('optimiza cachés', function () {
    artisan('setup:production', ['--force' => true, '--admin-email' => 'test@example.com'])
        ->expectsOutput('🧹 Limpiando y optimizando cachés...')
        ->expectsOutput('✅ Cachés optimizados')
        ->assertSuccessful();
});

it('verificaciones post-setup', function () {
    artisan('setup:production', ['--force' => true, '--admin-email' => 'test@example.com'])
        ->expectsOutput('✅ Verificaciones post-setup...')
        ->expectsOutput('✅ Usuario super-admin verificado')
        ->expectsOutput('✅ Roles y permisos verificados')
        ->expectsOutput('✅ Idiomas configurados')
        ->assertSuccessful();
});

it('confirma acción destructiva cuando no se usa --force', function () {
    artisan('setup:production')
        ->expectsConfirmation('¿Deseas continuar?', 'no')
        ->assertFailed();
});

it('solicita doble confirmación para migrate:fresh', function () {
    artisan('setup:production', ['--admin-email' => 'test@example.com'])
        ->expectsConfirmation('¿Deseas continuar?', 'yes')
        ->expectsConfirmation('¿Estás seguro de que deseas ejecutar migrate:fresh?', 'no')
        ->assertFailed();
});
