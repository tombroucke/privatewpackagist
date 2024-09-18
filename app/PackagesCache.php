<?php

namespace App;

use App\Events\CacheClearedEvent;
use App\Models\Package;
use Illuminate\Support\Facades\Cache;

class PackagesCache
{
    /**
     * The cache key.
     */
    private string $cacheKey = 'packages';

    /**
     * Regenerate the cache.
     */
    public function regenerate(): void
    {
        $packages = Package::all()
            ->mapWithKeys(fn ($package) => [
                $package->vendoredName() => (new PackageReleasesCache($package))->get(),
            ])
            ->reject(fn ($package) => $package->isEmpty())
            ->sortKeys()
            ->all();

        Cache::put($this->cacheKey, ['packages' => $packages]);
    }

    /**
     * Get the cache.
     */
    public function get(): array
    {
        if (! Cache::has($this->cacheKey)) {
            $this->regenerate();
        }

        return Cache::has($this->cacheKey)
            ? Cache::get($this->cacheKey)
            : [];
    }

    /**
     * Forget the cache.
     */
    public function forget(): void
    {
        event(new CacheClearedEvent($this->cacheKey));

        Cache::forget($this->cacheKey);
    }
}
