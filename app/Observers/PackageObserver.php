<?php

namespace App\Observers;

use App\Models\Package;

class PackageObserver
{
    public function creating(Package $package): void
    {
        $package->name = $package->updater()->fetchTitle();
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
        $package->name = $package->updater()->fetchTitle();
    }

    // private function createRelease(Package $package): void
    // {
    //     try {
    //         $release = $package->updater()->update();
    //     } catch (\Exception $e) {
    //         abort(403, "Failed to create release for {$package->slug}: {$e->getMessage()}");
    //     }

    //     if (! $release) {
    //         abort(403, "Failed to create release for {$package->slug}");
    //     }

    // }

    /**
     * Handle the Package "updated" event.
     */
    public function updated(Package $package): void
    {
        //
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
