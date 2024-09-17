<?php

namespace App;

use App\Events\CacheClearedEvent;
use App\Models\Package;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PackageReleasesCache
{
    /**
     * The cache key.
     */
    private string $cacheKey;

    /**
     * Create a new instance.
     */
    public function __construct(private Package $package)
    {
        $this->cacheKey = 'releases.'.$this->package->slug;
    }

    /**
     * Regenerate the cache.
     */
    public function regenerate(): void
    {
        $package = $this->package;

        $releases = $package->releases->mapwithKeys(fn ($release) => [
            $release->version => [
                'name' => $package->vendoredName(),
                'version' => $release->version,
                'type' => $package->type,
                'require' => [
                    'composer/installers' => '^1.0 || ^2.0',
                ],
                'dist' => [
                    'type' => 'zip',
                    'url' => asset('repo/'.$release->path),
                    'shasum' => sha1_file(storage_path('app/packages/'.$release->path)),
                ],
            ],
        ])->sortKeysDesc();

        Cache::put($this->cacheKey, $releases);
    }

    /**
     * Retrieve the cache.
     */
    public function get(): Collection
    {
        if (! Cache::has($this->cacheKey)) {
            $this->regenerate();
        }

        return Cache::has($this->cacheKey)
            ? Cache::get($this->cacheKey)
            : collect();
    }

    /**
     * Forget the cache.
     */
    public function forget(): void
    {
        event(new CacheClearedEvent($this->package->name));

        Cache::forget($this->cacheKey);
    }
}
