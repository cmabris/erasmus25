<?php

use App\Models\User;
use App\Support\Roles;
use Database\Seeders\ProductionAdminUserSeeder;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Saltar en modo paralelo - los tests de seeders que usan Artisan::call() no son compatibles
    // con ejecución paralela porque requieren SQLite en archivo persistente
    // Detectamos modo paralelo verificando si TEST_TOKEN está definido (Laravel lo establece automáticamente)
    if (getenv('TEST_TOKEN') !== false || isset($_SERVER['TEST_TOKEN'])) {
        $this->markTestSkipped('Los tests de seeders con comandos no se ejecutan en modo paralelo');
    }

    // Limpiar archivo de BD existente ANTES de configurar para evitar que RefreshDatabase
    // intente hacer VACUUM en un archivo con datos (lo cual falla en SQLite dentro de transacciones)
    $dbPath = database_path('testing_production_admin_user.sqlite');
    if (File::exists($dbPath)) {
        try {
            \Illuminate\Support\Facades\DB::disconnect('sqlite');
        } catch (\Exception $e) {
            // Ignorar errores si no hay conexión activa
        }
        File::delete($dbPath);
    }

    // Configurar SQLite en archivo persistente para evitar problemas con Artisan::call()
    // en subcomandos que abren nuevas conexiones
    // El archivo estará vacío, así que RefreshDatabase no intentará hacer VACUUM
    useSqliteFile('testing_production_admin_user.sqlite');
});

afterEach(function () {
    // Limpiar archivo de BD después de cada test para asegurar estado limpio para el siguiente
    // Esto evita que RefreshDatabase intente hacer VACUUM en el siguiente test
    $dbPath = database_path('testing_production_admin_user.sqlite');
    if (File::exists($dbPath)) {
        try {
            \Illuminate\Support\Facades\DB::disconnect('sqlite');
        } catch (\Exception $e) {
            // Ignorar errores si no hay conexión activa
        }
        @\Illuminate\Support\Facades\File::delete($dbPath);
    }
});

it('crea solo super-admin', function () {
    // Crear roles primero
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('newLine')->andReturnSelf();
    $mockCommand->shouldReceive('info')->andReturnSelf();
    $mockCommand->shouldReceive('line')->andReturnSelf();
    $mockCommand->shouldReceive('warn')->andReturnSelf();
    $seeder->setCommand($mockCommand);
    $seeder->email = 'test@example.com';
    $seeder->run();

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
    expect(User::count())->toBe(1); // Solo el super-admin
});

it('no crea otros usuarios', function () {
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::create(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::create(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::create(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('newLine')->andReturnSelf();
    $mockCommand->shouldReceive('info')->andReturnSelf();
    $mockCommand->shouldReceive('line')->andReturnSelf();
    $mockCommand->shouldReceive('warn')->andReturnSelf();
    $seeder->setCommand($mockCommand);
    $seeder->email = 'test@example.com';
    $seeder->run();

    // Verificar que solo existe el super-admin
    expect(User::count())->toBe(1);
    expect(User::where('email', 'admin@erasmus-murcia.es')->exists())->toBeFalse();
    expect(User::where('email', 'editor@erasmus-murcia.es')->exists())->toBeFalse();
    expect(User::where('email', 'viewer@erasmus-murcia.es')->exists())->toBeFalse();
});

it('solicita email por terminal si no se proporciona', function () {
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('ask')
        ->with('Introduce el email para el usuario super-admin')
        ->once()
        ->andReturn('test@example.com');
    $mockCommand->shouldReceive('newLine')->andReturnSelf();
    $mockCommand->shouldReceive('info')->andReturnSelf();
    $mockCommand->shouldReceive('line')->andReturnSelf();
    $mockCommand->shouldReceive('warn')->andReturnSelf();
    $seeder->setCommand($mockCommand);
    $seeder->run();

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('genera contraseña aleatoria segura', function () {
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('newLine')->andReturnSelf();
    $mockCommand->shouldReceive('info')->andReturnSelf();
    $mockCommand->shouldReceive('line')->andReturnSelf();
    $mockCommand->shouldReceive('warn')->andReturnSelf();
    $seeder->setCommand($mockCommand);
    $seeder->email = 'test@example.com';
    $seeder->run();

    expect($seeder->password)->not->toBeNull();
    expect(strlen($seeder->password))->toBeGreaterThanOrEqual(16);

    // Verificar que contiene diferentes tipos de caracteres
    $hasUppercase = preg_match('/[A-Z]/', $seeder->password);
    $hasLowercase = preg_match('/[a-z]/', $seeder->password);
    $hasNumbers = preg_match('/[0-9]/', $seeder->password);
    // Verificar símbolos (escapar correctamente los caracteres especiales)
    $hasSymbols = preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $seeder->password) ||
                  preg_match('/[^\w\s]/', $seeder->password); // Cualquier carácter no alfanumérico ni espacio

    expect($hasUppercase)->toBe(1);
    expect($hasLowercase)->toBe(1);
    expect($hasNumbers)->toBe(1);
    expect($hasSymbols)->toBeGreaterThan(0); // Puede ser 1 o true
});

it('valida formato de email', function () {
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('error')->once();
    $seeder->setCommand($mockCommand);
    $seeder->email = 'email-invalido';

    $seeder->run();

    expect(User::where('email', 'email-invalido')->exists())->toBeFalse();
});

it('no duplica usuarios existentes', function () {
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);

    // Crear usuario existente
    User::factory()->create(['email' => 'test@example.com']);

    $initialCount = User::count();

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('warn')->once();
    $seeder->setCommand($mockCommand);
    $seeder->email = 'test@example.com';
    $seeder->run();

    // No debe crear un nuevo usuario
    expect(User::count())->toBe($initialCount);
});

it('asigna rol super-admin correctamente', function () {
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('newLine')->andReturnSelf();
    $mockCommand->shouldReceive('info')->andReturnSelf();
    $mockCommand->shouldReceive('line')->andReturnSelf();
    $mockCommand->shouldReceive('warn')->andReturnSelf();
    $seeder->setCommand($mockCommand);
    $seeder->email = 'test@example.com';
    $seeder->run();

    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasRole(Roles::SUPER_ADMIN))->toBeTrue();
});

it('muestra credenciales al finalizar', function () {
    Role::create(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);

    $seeder = new ProductionAdminUserSeeder;
    $mockCommand = \Mockery::mock(Command::class);
    $mockCommand->shouldReceive('newLine')->atLeast()->once();
    $mockCommand->shouldReceive('info')
        ->with('✅ Usuario super-admin creado correctamente')
        ->once();
    $mockCommand->shouldReceive('line')
        ->with('📋 Credenciales:')
        ->once();
    $mockCommand->shouldReceive('line')
        ->with(Mockery::pattern('/Email: test@example.com/'))
        ->once();
    $mockCommand->shouldReceive('line')
        ->with(Mockery::pattern('/Contraseña:/'))
        ->once();
    $mockCommand->shouldReceive('warn')
        ->with('⚠️  IMPORTANTE:')
        ->once();
    $mockCommand->shouldReceive('line')->andReturnSelf();
    $seeder->setCommand($mockCommand);
    $seeder->email = 'test@example.com';

    $seeder->run();
});
