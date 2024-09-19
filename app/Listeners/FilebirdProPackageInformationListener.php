<?php

namespace App\Listeners;

use App\Events\PackageInformationEvent;
use Illuminate\Support\Str;

class FilebirdProPackageInformationListener
{
    /**
     * Handle the event.
     */
    public function handle(PackageInformationEvent $event): void
    {
        if (
            ! $event->package->settings ||
            ! is_array($event->package->settings) ||
            ($event->package->settings['slug'] ?? null) !== 'filebird_pro'
        ) {
            return;
        }

        if (! Str::contains($event->packageInformation['downloadLink'], ['code=', 'domain='])) {
            return;
        }

        $code = $event->package->secrets()->get('license_key');
        $email = getenv('FILEBIRD_PRO_EMAIL');
        $url = $event->package->settings['source_url'];

        $domain = Str::of($url)->replace(['http://', 'https://'], '')->rtrim('/');

        $character = Str::contains($event->packageInformation['downloadLink'], '?')
            ? '?'
            : '&';

        $event->packageInformation['downloadLink'] .= "{$character}code={$code}&domain={$domain}&email={$email}";
    }
}
