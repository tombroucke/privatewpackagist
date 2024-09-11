<?php

namespace App\Observers;

use App\Models\Release;
use App\PackagesJson;

class ReleaseObserver
{
    /**
     * Handle the Release "created" event.
     */
    public function created(Release $release): void
    {
        PackagesJson::regenerate();
    }

    /**
     * Handle the Release "updated" event.
     */
    public function updated(Release $release): void
    {
        PackagesJson::regenerate();
    }

    /**
     * Handle the Package "deleted" event.
     */
    public function deleted(Release $release): void
    {
        PackagesJson::regenerate();
    }

    /**
     * Handle the Package "restored" event.
     */
    public function restored(Release $release): void
    {
        PackagesJson::regenerate();
    }

    /**
     * Handle the Package "force deleted" event.
     */
    public function forceDeleted(Release $release): void
    {
        PackagesJson::regenerate();
    }
}
