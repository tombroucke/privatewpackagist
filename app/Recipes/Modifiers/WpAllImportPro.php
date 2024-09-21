<?php

namespace App\Recipes\Modifiers;

use App\Events\LicenseValidatedEvent;
use Illuminate\Support\Str;

class WpAllImportPro
{
    public function modifyLicenseValidation(LicenseValidatedEvent $event): void
    {
        if (! Str::contains($event->package->settings['endpoint_url'], 'wpallimport.com')) {
            return;
        }

        $event->valid = $event->params['response']['msg'] === 'Success.';
        if (! $event->valid) {
            $event->message = Str::replace('No license key has been provided.', 'No valid license key has been provided.', $event->params['response']['msg']);
        }
    }
}
