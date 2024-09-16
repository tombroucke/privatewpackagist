<?php

namespace App\Observers;

use App\Models\Package;
use App\PackagesJson;

class PackageObserver
{
    public function creating(Package $package): void
    {
        $package->name = $package->updater()->fetchPackageTitle();
    }

    /**
     * Handle the Package "created" event.
     */
    public function created(Package $package): void
    {
        //
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updating(Package $package): void
    {
        $package->name = $package->updater()->fetchPackageTitle();
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updated(Package $package): void
    {
        app()->make(PackagesJson::class)->regenerate();
    }

    /**
     * Handle the Package "deleted" event.
     */
    public function deleted(Package $package): void
    {
        //
    }

    /**
     * Handle the Package "restored" event.
     */
    public function restored(Package $package): void
    {
        //
    }

    /**
     * Handle the Package "force deleted" event.
     */
    public function forceDeleted(Package $package): void
    {
        //
    }
}
