<?php

namespace App\Providers;

use App\Models\Package;
use App\Models\Release;
use App\Observers\PackageObserver;
use App\Observers\ReleaseObserver;
use App\PackagesJson;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PackagesJson::class, function ($app) {
            return new PackagesJson;
        });

        $this->app->singleton('updaters', function ($app) {
            // glob() is used to get all the files in the directory
            $updaters = glob(app_path('Updaters/*.php'));

            return collect($updaters)->mapWithKeys(function ($updater) {
                $className = 'App\\Updaters\\'.basename($updater, '.php');

                return [($className)::slug() => $className];
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Package::observe(PackageObserver::class);
        Release::observe(ReleaseObserver::class);
    }
}
