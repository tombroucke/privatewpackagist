<?php

namespace App\Updaters;

use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Edd implements Contracts\Updater
{
    use Concerns\ExtractsChangelog;
    use Concerns\StoresDownload;

    const ENV_VARIABLES = [
        'LICENSE_KEY',
    ];

    public function __construct(private Package $package) {}

    public function fetchTitle(): string
    {
        $response = $this->doEddAction('check_license');

        return strip_tags($response['item_name']) ?? Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        $this->activateLicense();

        if (! $this->checkLicense()) {
            $errors->push('Invalid license');
        }

        return $errors;
    }

    private function licenseKey(): string
    {
        return $this->package->environmentVariable('LICENSE_KEY');
    }

    private function activateLicense(): void
    {
        $this->doEddAction('activate_license');

    }

    private function checkLicense(): bool
    {
        $response = $this->doEddAction('check_license');

        return $response['license'] === 'valid';
    }

    private function doEddAction(string $action): array
    {
        $response = Http::get($this->package->settings['endpoint_url'], [
            'edd_action' => $action,
            'license' => $this->licenseKey(),
            'item_name' => $this->package->settings['slug'],
            'url' => $this->package->settings['source_url'],
        ]);

        $body = $response->body();

        return json_decode($body, true);
    }

    public function createRelease(): ?Release
    {
        if (! $this->checkLicense()) {
            return null;
        }

        $response = $this->doEddAction('get_version');

        $version = $response['new_version'];
        $sections = unserialize($response['sections']);

        $pattern = $this->package->settings['changelog_extract'] ?? '\*\*(\d+\.\d+\.\d+) \((.*?)\)\*\*\n(.*?)\n\n';

        $changelog = $this->extractLatestChangelog($sections['changelog'] ?? '', $pattern);
        $downloadLink = $response['download_link'];
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
}
