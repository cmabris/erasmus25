<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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

        // Registrar RolePolicy manualmente (el modelo no está en App\Models)
        Gate::policy(Role::class, RolePolicy::class);
    }
}
