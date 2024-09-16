<?php

namespace App;

use App\Events\CacheClearedEvent;
use App\Models\Package;
use Illuminate\Support\Facades\Cache;

class PackageReleasesCache
{
    private string $cacheKey;

    public function __construct(private Package $package)
    {
        $this->cacheKey = 'releases.'.$this->package->slug;
    }

    public function regenerate()
    {
        $package = $this->package;
        $releases = $package->releases
            ->mapwithKeys(function ($release) use ($package) {
                return [$release->version => [
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
                ]];
            })
            ->sortKeysDesc();

        Cache::put($this->cacheKey, $releases);
    }

    public function get()
    {
        if (! Cache::has($this->cacheKey)) {
            $this->regenerate();
        }

        return Cache::has($this->cacheKey) ? Cache::get($this->cacheKey) : [];
    }

    public function forget()
    {
        event(new CacheClearedEvent($this->package->name));
        Cache::forget($this->cacheKey);
    }
}
