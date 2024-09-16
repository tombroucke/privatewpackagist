<?php

namespace App;

use App\Events\CacheClearedEvent;
use App\Models\Package;
use Illuminate\Support\Facades\Cache;

class PackagesCache
{
    private string $cacheKey = 'packages';

    public function __construct() {}

    public function regenerate(): void
    {
        $packages = Package::all()
            ->mapWithKeys(function ($package) {
                return [$package->vendoredName() => (new PackageReleasesCache($package))->get()];
            })
            ->reject(function ($package) {
                return $package->isEmpty();
            })
            ->sortKeys();

        $output = [
            'packages' => $packages->toArray(),
        ];

        Cache::put($this->cacheKey, $output);
    }

    public function get(): array
    {
        if (! Cache::has($this->cacheKey)) {
            $this->regenerate();
        }

        return Cache::has($this->cacheKey) ? Cache::get($this->cacheKey) : [];
    }

    public function forget(): void
    {
        event(new CacheClearedEvent($this->cacheKey));
        Cache::forget($this->cacheKey);
    }
}
