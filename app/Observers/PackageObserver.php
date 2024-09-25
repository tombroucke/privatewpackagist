<?php

namespace App\Observers;

use App\Models\Package;
use App\PackageReleasesCache;
use App\PackagesCache;
use App\Recipes\Exceptions\ShouldNotBeAutomaticallyUpdatedException;
use Exception;
use Filament\Notifications\Notification;

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
        $errors = $package->validationErrors();

        if ($errors->isNotEmpty()) {
            $errors->each(fn ($error) => Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body($error)
                ->send()
            );
        } else {
            if (is_null($package->license_valid_from)) {
                $package->update([
                    'license_valid_from' => now(),
                ]);
            }
            $this->createRelease($package);
        }
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updating(Package $package)
    {
        $package->name = $package->recipe()->fetchPackageTitle();
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updated(Package $package): void
    {
        $errors = $package->validationErrors();

        if ($errors->isNotEmpty()) {
            $errors->each(fn ($error) => Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body($error)
                ->send()
            );
            if ($package->license_valid_from && is_null($package->license_valid_to)) {
                $package->updateQuietly([
                    'license_valid_to' => now(),
                ]);
            }
        } else {
            if (is_null($package->license_valid_from)) {
                $package->license_valid_from = now();
            }
            $package->license_valid_to = null;
            $package->saveQuietly();

            $this->createRelease($package);

            (new PackageReleasesCache($package))->forget();

            app()->make(PackagesCache::class)->forget();
        }
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

    private function createRelease(Package $package): void
    {
        try {
            $release = $package->recipe()->update();
        } catch (ShouldNotBeAutomaticallyUpdatedException $e) {
            // Do nothing, the package can not be automatically updated
        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->send();
        }

        if (isset($release) && $release->wasRecentlyCreated) {
            Notification::make()
                ->success()
                ->title('Release created')
                ->body("{$package->name} {$release->version} has been released")
                ->send();
        }
    }
}
