<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cargar helpers personalizados
        if (file_exists($helperPath = base_path('app/Support/helpers.php'))) {
            require_once $helperPath;
        }

        // Registrar directiva Blade para traducciones dinámicas
        Blade::directive('trans', function ($expression) {
            return "<?php echo trans_model($expression) ?? ''; ?>";
        });
    }
}
