<?php

namespace App\Updaters;

use App\Exceptions\PucLicenceCheckFailed;
use App\Exceptions\PucNoDownloadLinkException;
use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Puc implements Contracts\Updater
{
    use Concerns\CreatesRelease;
    use Concerns\ExtractsChangelog;

    const ENV_VARIABLES = [
        'LICENSE_KEY',
    ];

    public function __construct(private Package $package) {}

    public function fetchTitle(): string
    {
        return Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        return $errors;
    }

    private function userAgent()
    {
        return sprintf('WordPress/6.6.2; %s',
            $this->package->settings['source_url'],
        );
    }

    private function licenseKey(): string
    {
        return $this->package->environmentVariable('LICENSE_KEY');
    }

    public function update(): ?Release
    {
        $licenseCheck = $this->doWpAction('licensecheck');

        if (($licenseCheck['license_check'] ?? true) !== true) {
            throw new PucLicenceCheckFailed;
        }

        $packageInformation = $this->doWpAction('updatecheck');

        if (! isset($packageInformation['download_url']) || $packageInformation === '') {
            throw new PucNoDownloadLinkException;
        }

        $version = $packageInformation['version'];
        $downloadLink = $packageInformation['download_url'];

        return $this->createRelease($version, $downloadLink, '');
    }

    public function doWpAction(string $action)
    {
        $response = Http::withUserAgent($this->userAgent())->get($this->package->settings['endpoint_url'], [
            'wpaction' => $action,
            'dlid' => $this->licenseKey(),
            'wpslug' => $this->package->settings['slug'],
        ]);

        return $response->json();
    }
}
