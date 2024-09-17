<?php

namespace App\Providers;

use App\Events\PackageInformationEvent;
use App\Listeners\FilebirdProPackageInformationListener;
use App\Models\Package;
use App\Models\Release;
use App\Observers\PackageObserver;
use App\Observers\ReleaseObserver;
use App\PackagesCache;
use App\Recipes\Recipe;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PackagesCache::class, fn ($app) => new PackagesCache);

        $this->app->singleton('recipes', function ($app) {
            $path = config('packagist.recipes.path');
            $namespace = Str::finish(config('packagist.recipes.namespace'), '\\');

            $recipes = collect(File::allFiles($path))
                ->filter(fn ($file) => $file->getExtension() === 'php')
                ->map(fn ($file) => Str::of($file->getPathname())
                    ->replace([$path, '.php'], '')
                    ->ltrim('/')
                    ->start($namespace)
                    ->toString()
                );

            return $recipes
                ->filter(fn ($recipe) => is_subclass_of($recipe, Recipe::class))
                ->reject(fn ($recipe) => (new ReflectionClass($recipe))->isAbstract())
                ->mapWithKeys(fn ($recipe) => [$recipe::slug() => $recipe]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Package::observe(PackageObserver::class);
        Release::observe(ReleaseObserver::class);
        Event::listen(PackageInformationEvent::class, FilebirdProPackageInformationListener::class);
    }
}
