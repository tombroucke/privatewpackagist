<?php

namespace App\Updaters;

use App\Exceptions\IncorrectApiResponseCodeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GravityForms extends Abstracts\Updater implements Contracts\Updater
{
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
            $errors->push('Env. variable GRAVITYFORMS_LICENSE_KEY is required');
        }

        return $errors;
    }

    protected function packageInformation(): array
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

        return [$version, $changelog, $downloadLink];
    }
}
