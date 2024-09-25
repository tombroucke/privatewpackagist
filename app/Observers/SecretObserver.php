<?php

namespace App\Observers;

use App\Models\Secret;

class SecretObserver
{
    /**
     * Handle the Secret "updated" event.
     */
    public function updated(Secret $secret): void
    {
        $secret->packages->each->touch(); // Trigger validation
    }

    /**
     * Handle the Secret "deleted" event.
     */
    public function deleting(Secret $secret): void
    {
        $packages = $secret->packages;
        $secret->packages()->detach();
        $packages->each->touch(); // Trigger validation
    }
}
