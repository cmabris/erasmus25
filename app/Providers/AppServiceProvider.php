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
use Illuminate\Database\Eloquent\Model;
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
        // Configurar protección contra lazy loading en desarrollo
        // Esto lanzará una excepción si se detecta un problema N+1
        $this->configureModelStrictness();

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

    /**
     * Configure model strictness settings for development.
     *
     * - preventLazyLoading: Throws exception on N+1 queries (dev only)
     * - preventSilentlyDiscardingAttributes: Throws exception on mass assignment issues
     */
    protected function configureModelStrictness(): void
    {
        // Solo en entornos de desarrollo y testing
        $shouldBeStrict = ! $this->app->isProduction();

        // Prevenir lazy loading (detecta N+1)
        Model::preventLazyLoading($shouldBeStrict);

        // Prevenir que se descarten atributos silenciosamente en mass assignment
        Model::preventSilentlyDiscardingAttributes($shouldBeStrict);

        // NO activamos preventAccessingMissingAttributes porque puede causar problemas
        // con modelos que usan SoftDeletes donde deleted_at puede no estar siempre cargado
        // Model::preventAccessingMissingAttributes($shouldBeStrict);
    }
}
