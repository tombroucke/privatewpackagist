<?php

namespace App\Listeners;

use App\Events\PackageInformationEvent;

class FilebirdProPackageInformationListener
{
    /**
     * Handle the event.
     */
    public function handle(PackageInformationEvent $event): void
    {
        if ($event->package->settings && is_array($event->package->settings) && ($event->package->settings['slug'] ?? null) === 'filebird_pro') {
            if (strpos($event->packageInformation['downloadLink'], 'code=') !== false || strpos($event->packageInformation['downloadLink'], 'domain=') !== false) {
                return;
            }

            $code = $event->package->updater()->licenseKey();
            $email = getenv('FILEBIRD_PRO_EMAIL');
            $url = $event->package->settings['source_url'];
            $domain = str_replace(['http://', 'https://'], '', $url);
            $cleanDomain = rtrim($domain, '/');

            $concatCharacter = strpos($event->packageInformation['downloadLink'], '?') === false ? '?' : '&';
            $event->packageInformation['downloadLink'] .= $concatCharacter.'code='.$code.'&domain='.$cleanDomain.'&email='.$email;
        }
    }
}
