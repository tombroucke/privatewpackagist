<?php

namespace App\Updaters;

use App\Exceptions\WpRocketInvalidRequestException;
use App\Exceptions\WpRocketUnexpectedResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WpRocket extends Abstracts\Updater implements Contracts\Updater
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

        if (! env('WP_ROCKET_KEY')) {
            $errors->push('Env. variable WP_ROCKET_KEY is required');
        }

        if (! env('WP_ROCKET_EMAIL')) {
            $errors->push('Env. variable WP_ROCKET_EMAIL is required');
        }

        if (! env('WP_ROCKET_URL')) {
            $errors->push('Env. variable WP_ROCKET_URL is required');
        }

        return $errors;
    }

    public function userAgent(): string
    {
        return sprintf('%s; %1$s;WP-Rocket|3.6.3|%2$s|%3$s|%1$s|8.2;',
            config('app.wp_user_agent'),
            getenv('WP_ROCKET_URL'),
            getenv('WP_ROCKET_KEY'),
            getenv('WP_ROCKET_EMAIL'),
        );
    }

    public function fetchZip(string $link): string
    {
        return Http::withUserAgent($this->userAgent())->get($link)->body();
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
        $response = Http::withUserAgent($this->userAgent())->get('https://api.wp-rocket.me/check_update.php');
        $body = $response->body();

        $jsonResponse = $response->json();
        if (is_array($jsonResponse) && $jsonResponse['success'] === false) {
            throw new WpRocketInvalidRequestException($jsonResponse['data']['reason']);
        }

        if (! preg_match('@^(?<stable_version>\d+(?:\.\d+){1,3}[^|]*)\|(?<package>(?:http.+\.zip)?)\|(?<user_version>\d+(?:\.\d+){1,3}[^|]*)(?:\|+)?$@', $body, $match)) {
            throw new WpRocketUnexpectedResponseException;
        }

        $version = $match['user_version'];

        $downloadLink = sprintf(
            'https://api.wp-rocket.me/%s/wp-rocket_%s.zip',
            getenv('WP_ROCKET_KEY'),
            $version,
        );

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
