<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Tenancy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Tenancy::class);
    }

    public function boot(): void
    {
        if (config('app.env') === 'production' || str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        Paginator::useBootstrapFive();

        // Compartir la configuración del colegio con todas las vistas.
        // Protegido para no fallar antes de migrar la base de datos.
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $view->with('appSettings', Setting::current());
                }
            } catch (\Throwable $e) {
                // Base de datos no disponible aún (migraciones, etc.)
            }
        });
    }
}
