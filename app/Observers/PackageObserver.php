<?php

namespace App\Observers;

use App\Models\Package;
use App\PackageReleasesCache;
use App\PackagesCache;

class PackageObserver
{
    /**
     * Handle the Package "creating" event.
     */
    public function creating(Package $package): void
    {
        $package->name = $package->recipe()->fetchPackageTitle();
    }

    /**
     * Handle the Package "created" event.
     */
    public function created(Package $package): void
    {
        (new PackageReleasesCache($package))->forget();

        app()->make(PackagesCache::class)->forget();
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updating(Package $package): void
    {
        $package->name = $package->recipe()->fetchPackageTitle();
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updated(Package $package): void
    {
        (new PackageReleasesCache($package))->forget();

        app()->make(PackagesCache::class)->forget();
    }

    /**
     * Handle the Package "deleted" event.
     */
    public function deleted(Package $package): void
    {
        app()->make(PackagesCache::class)->forget();
    }

    /**
     * Handle the Package "restored" event.
     */
    public function restored(Package $package): void
    {
        app()->make(PackagesCache::class)->forget();
    }

    /**
     * Handle the Package "force deleted" event.
     */
    public function forceDeleted(Package $package): void
    {
        app()->make(PackagesCache::class)->forget();
    }
}
