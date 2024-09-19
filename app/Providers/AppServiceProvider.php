<?php

namespace App\Providers;

use App\Events\PackageInformationEvent;
use App\Events\RecipeFormsCollectedEvent;
use App\Models\Package;
use App\Models\Release;
use App\Models\Token;
use App\Observers\PackageObserver;
use App\Observers\ReleaseObserver;
use App\Observers\TokenObserver;
use App\PackageDownloader;
use App\PackagesCache;
use App\Recipes\Recipe;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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

        $this->app->make('recipes')
            ->each(function ($recipe, $slug) {
                $this->app->bind($recipe, function ($app, $params) use ($recipe) {
                    $httpClient = new Http;

                    return new $recipe($params['package'], $httpClient);
                });
            });

        $this->app->bind(PackageDownloader::class, function ($app, $params) {
            $httpClient = new Http;

            return new PackageDownloader($params['recipe'], $httpClient);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Package::observe(PackageObserver::class);
        Release::observe(ReleaseObserver::class);
        Token::observe(TokenObserver::class);

        $this->registerModifiers();

    }

    /**
     * Register recipe modifiers.
     */
    private function registerModifiers()
    {
        $path = config('packagist.recipes.path').'/Modifiers';
        $namespace = Str::finish(config('packagist.recipes.namespace'), '\\Modifiers\\');

        $modifiers = collect(File::allFiles($path))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => Str::of($file->getPathname())
                ->replace([$path, '.php'], '')
                ->ltrim('/')
                ->start($namespace)
                ->toString()
            );

        $modifiers->reject(function ($recipe) {
            try {
                return (new ReflectionClass($recipe))->isInterface();
            } catch (\Throwable $th) {
                return true;
            }
        })->each(function ($modifier) {
            Event::listen(RecipeFormsCollectedEvent::class, [$modifier, 'modifyRecipeForms']);
            Event::listen(PackageInformationEvent::class, [$modifier, 'modifyPackageInformation']);
        });
    }
}
