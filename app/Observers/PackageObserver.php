<?php

namespace App\Observers;

use App\Models\Package;

class PackageObserver
{
    public function creating(Package $package): void
    {
        $package->name = $package->updater()->fetchTitle();
        $this->validate($package);
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
        $this->validate($package);
    }

    public function validate(Package $package)
    {
        $errors = collect();
        $updater = $package->updater();

        $package
            ->environmentVariables()
            ->each(function ($value, $key) use ($errors, $package) {
                if (empty($value)) {
                    $errors->push("{$package->prefixedVariable($key)} is required");
                }
            });

        if ($updater->validationErrors()->isNotEmpty()) {
            $errors = $errors->merge($updater->validationErrors());
        }

        if ($errors->isNotEmpty()) {
            abort(403, $errors->first());
        }
    }

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
