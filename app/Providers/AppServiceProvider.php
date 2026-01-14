<?php

namespace App\Providers;

use App\Models\Call;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Resolution;
use App\Observers\CallObserver;
use App\Observers\DocumentObserver;
use App\Observers\NewsPostObserver;
use App\Observers\ResolutionObserver;
use App\Policies\ActivityPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
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

        // Registrar ActivityPolicy manualmente (el modelo es de Spatie)
        Gate::policy(Activity::class, ActivityPolicy::class);

        // Registrar Observers
        Call::observe(CallObserver::class);
        Resolution::observe(ResolutionObserver::class);
        NewsPost::observe(NewsPostObserver::class);
        Document::observe(DocumentObserver::class);
    }
}
