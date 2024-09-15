<?php

namespace App\Updaters;

use App\Exceptions\AcfFailedToGetLatestVersionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Acf extends Abstracts\Updater
{
    private array $packageInformation;

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

        if (! env('ACF_LICENSE_KEY')) {
            $errors->push('Env. variable ACF_LICENSE_KEY is required');
        }

        return $errors;
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
        $version = $this->getLatestVersion();

        if (! $version) {
            throw new AcfFailedToGetLatestVersionException;
        }

        $downloadLink = sprintf(
            'https://connect.advancedcustomfields.com/v2/plugins/download?t=%s&p=pro&k=%s',
            $version,
            getenv('ACF_LICENSE_KEY'),
        );

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }

    private function getLatestVersion()
    {
        $packages = Http::get('https://connect.advancedcustomfields.com/packages.json')->json();

        if (! is_array($packages) || ! isset($packages['packages']['wpengine/advanced-custom-fields-pro'])) {
            return null;
        }

        return array_key_first($packages['packages']['wpengine/advanced-custom-fields-pro']);
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
