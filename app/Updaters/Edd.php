<?php

namespace App\Updaters;

use App\Exceptions\EddLicenseCheckFailedException;
use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Edd implements Contracts\Updater
{
    use Concerns\CreatesRelease;
    use Concerns\ExtractsChangelog;

    const ENV_VARIABLES = [
        'LICENSE_KEY',
    ];

    private bool $skipLicenseCheck = false;

    public function __construct(private Package $package)
    {
        $this->skipLicenseCheck = isset($this->package->settings['skip_license_check']) && $this->package->settings['skip_license_check'];
    }

    public function fetchTitle(): string
    {
        $response = $this->doEddAction('check_license');

        $name = $response['item_name'] ?? $response['name'] ?? Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();

        return strip_tags($name);
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        $this->activateLicense();
        if (! $this->skipLicenseCheck && ! $this->checkLicense()) {
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

        return isset($response['license']) && $response['license'] === 'valid';
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

    public function update(): ?Release
    {
        if (! $this->skipLicenseCheck && ! $this->checkLicense()) {
            throw new EddLicenseCheckFailedException;
        }

        $response = $this->doEddAction('get_version');

        $version = $response['new_version'];
        $sections = @unserialize($response['sections']);

        $pattern = $this->package->settings['changelog_extract'] ?? '\*\*(\d+\.\d+\.\d+) \((.*?)\)\*\*\n(.*?)\n\n';

        $changelog = $this->extractLatestChangelog($sections['changelog'] ?? '', $pattern);
        $downloadLink = $response['download_link'];

        return $this->createRelease($version, $downloadLink, $changelog);
    }
}
