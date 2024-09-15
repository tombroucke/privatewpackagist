<?php

namespace App\Updaters;

use App\Exceptions\PucLicenceCheckFailed;
use App\Exceptions\PucNoDownloadLinkException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Puc extends Abstracts\Updater implements Contracts\Updater
{
    private array $packageInformation;

    const ENV_VARIABLES = [
        'LICENSE_KEY',
    ];

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

    public function userAgent(): string
    {
        return sprintf('%s; %s',
            config('app.wp_user_agent'),
            $this->package->settings['source_url'],
        );
    }

    private function licenseKey(): string
    {
        return $this->package->environmentVariable('LICENSE_KEY');
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

    private function getPackageInformation(string $key): ?string
    {
        if (! isset($this->packageInformation)) {
            $this->packageInformation = $this->fetchPackageInformation();
        }

        return $this->packageInformation[$key] ?? null;
    }

    private function fetchPackageInformation(): array
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

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }

    public function version(): ?string
    {
        return $this->getPackageInformation('version');
    }

    public function downloadLink(): ?string
    {
        return $this->getPackageInformation('downloadLink');
    }

    public function changelog(): ?string
    {
        return $this->getPackageInformation('changelog');
    }
}
