<?php

namespace App\Observers;

use App\Models\Package;
use App\Models\Release;
use App\PackageReleasesCache;
use App\PackagesCache;

class ReleaseObserver
{
    /**
     * Handle the Release "created" event.
     */
    public function created(Release $release): void
    {
        $this->clearPackageCache($release->package);
    }

    /**
     * Handle the Release "updated" event.
     */
    public function updated(Release $release): void
    {
        $this->clearPackageCache($release->package);
    }

    /**
     * Handle the Package "deleted" event.
     */
    public function deleted(Release $release): void
    {
        $this->clearPackageCache($release->package);
    }

    /**
     * Handle the Package "restored" event.
     */
    public function restored(Release $release): void
    {
        $this->clearPackageCache($release->package);
    }

    /**
     * Handle the Package "force deleted" event.
     */
    public function forceDeleted(Release $release): void
    {
        $this->clearPackageCache($release->package);
    }

    /**
     * Clear the package cache.
     */
    private function clearPackageCache(Package $package): void
    {
        (new PackageReleasesCache($package))->forget();

        app()->make(PackagesCache::class)->forget();
    }
}
