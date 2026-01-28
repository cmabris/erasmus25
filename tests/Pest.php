<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

// Configuración para Browser Tests
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Browser');

// Configurar modo headed por defecto solo en desarrollo local (no en CI)
if (! env('CI')) {
    pest()->browser()->headed();
}

/*
|--------------------------------------------------------------------------
| Global Test Setup
|--------------------------------------------------------------------------
|
| This closure runs before each test in Feature tests to ensure a clean state.
|
*/

beforeEach(function () {
    // Clear translation-related caches to avoid interference between parallel tests
    \Illuminate\Support\Facades\Cache::forget('translations.active_languages');
    \Illuminate\Support\Facades\Cache::forget('translations.active_programs');
    \Illuminate\Support\Facades\Cache::forget('translations.all_settings');
})->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Helper function to create an Excel file for testing imports.
 */
function createExcelFile(array $data): \Illuminate\Http\UploadedFile
{
    $filename = 'test-import-'.uniqid().'.xlsx';
    $tempPath = sys_get_temp_dir().'/'.$filename;

    // Create Excel file using Laravel Excel
    $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray
    {
        public function __construct(protected array $data) {}

        public function array(): array
        {
            return $this->data;
        }
    };

    // Use Excel::download to create file, then read it
    $filePath = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

    // Save to temp file
    file_put_contents($tempPath, $filePath);

    // Create UploadedFile from the stored file
    $file = new \Illuminate\Http\UploadedFile(
        $tempPath,
        $filename,
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true // test mode
    );

    return $file;
}

/**
 * Configura SQLite en memoria (comportamiento por defecto para la mayoría de tests).
 *
 * Esta función restaura la configuración por defecto de SQLite en memoria,
 * que es la utilizada por la mayoría de tests de la aplicación.
 */
function useSqliteInMemory(): void
{
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
}

/**
 * Configura SQLite en archivo persistente (necesario para tests que usan Artisan::call()).
 *
 * Los tests que utilizan Artisan::call() con subcomandos (como migrate:fresh) que abren
 * nuevas conexiones a la base de datos requieren un archivo persistente en lugar de :memory:,
 * ya que con :memory: la base de datos desaparece entre conexiones.
 *
 * @param  string  $filename  Nombre del archivo de BD (por defecto 'testing_command.sqlite')
 */
function useSqliteFile(string $filename = 'testing_command.sqlite'): void
{
    $dbPath = database_path($filename);

    // Crear archivo vacío si no existe
    if (! \Illuminate\Support\Facades\File::exists($dbPath)) {
        \Illuminate\Support\Facades\File::put($dbPath, '');
    }

    // Configurar la conexión SQLite para usar el archivo persistente
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', $dbPath);

    // Forzar reconexión para que use la nueva configuración
    // Esto es crítico para que Artisan::call() use la misma BD
    \Illuminate\Support\Facades\DB::purge('sqlite');
    \Illuminate\Support\Facades\DB::reconnect('sqlite');
}
