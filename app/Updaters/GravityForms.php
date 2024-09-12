<?php

namespace App\Updaters;

use App\Exceptions\DownloadLinkNotSetException;
use App\Exceptions\IncorrectApiResponseCodeException;
use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GravityForms implements Contracts\Updater
{
    use Concerns\CreatesRelease;
    use Concerns\ExtractsChangelog;

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

        if (! env('GRAVITYFORMS_LICENSE_KEY')) {
            $errors->push('GRAVITYFORMS_LICENSE_KEY is required');
        }

        return $errors;
    }

    public function update(): ?Release
    {
        $url = sprintf(
            'https://gravityapi.com/wp-content/plugins/gravitymanager/api.php?op=get_plugin&slug=%s&key=%s',
            $this->package->settings['slug'],
            getenv('GRAVITYFORMS_LICENSE_KEY'),
        );

        $response = Http::get($url);

        if ($response->status() !== 200) {
            throw new IncorrectApiResponseCodeException;
        }

        $body = $response->body();
        $packageInformation = unserialize($body);

        $version = $packageInformation['version'];
        $downloadLink = $packageInformation['download_url_latest'];
        $changelog = $this->extractLatestChangelog($packageInformation['changelog'], 'Gravity Forms v[\d.]+ Changelog\s*-+\s*((?:-.*\n)+)');

        try {
            return $this->createRelease($version, $downloadLink, $changelog);
        } catch (DownloadLinkNotSetException $e) {
            throw new \Exception('Download link not set. Is GRAVITYFORMS_LICENSE_KEY active & correct?');
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
