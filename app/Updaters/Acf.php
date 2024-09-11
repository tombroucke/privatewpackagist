<?php

namespace App\Updaters;

use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Acf implements Contracts\Updater
{
    use Concerns\ExtractsChangelog;
    use Concerns\StoresDownload;

    const ENV_VARIABLES = [
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

        if (! env('ACF_LICENSE_KEY')) {
            $errors->push('ACF_LICENSE_KEY is required');
        }

        return $errors;
    }

    public function createRelease(): ?Release
    {
        $version = $this->getLatestVersion();

        if (! $version) {
            return null;
        }

        $changelog = '';
        $downloadLink = sprintf(
            'https://connect.advancedcustomfields.com/v2/plugins/download?t=%s&p=pro&k=%s',
            $version,
            getenv('ACF_LICENSE_KEY'),
        );

        $existingRelease = $this->package->releases()->where('version', $version)->first();
        if ($existingRelease) {
            return $existingRelease;
        }

        $filePath = $this->storeDownload($this->package, $downloadLink, $version);

        return $this->package->releases()->create([
            'version' => $version,
            'changelog' => $changelog,
            'path' => $filePath,
        ]);
    }

    private function getLatestVersion()
    {
        $packages = Http::get('https://connect.advancedcustomfields.com/packages.json')->json();

        if (! is_array($packages) || ! isset($packages['packages']['wpengine/advanced-custom-fields-pro'])) {
            return null;
        }

        return array_key_first($packages['packages']['wpengine/advanced-custom-fields-pro']);
    }
}
