<?php

namespace App\Updaters;

use App\Exceptions\WpRocketInvalidRequestException;
use App\Exceptions\WpRocketUnexpectedResponseException;
use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WpRocket implements Contracts\Updater
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

        if (! env('WP_ROCKET_KEY')) {
            $errors->push('WP_ROCKET_KEY is required');
        }

        if (! env('WP_ROCKET_EMAIL')) {
            $errors->push('WP_ROCKET_EMAIL is required');
        }

        if (! env('WP_ROCKET_URL')) {
            $errors->push('WP_ROCKET_URL is required');
        }

        return $errors;
    }

    private function userAgent()
    {
        return sprintf('WordPress/6.6.2; %1$s;WP-Rocket|3.6.3|%2$s|%3$s|%1$s|8.2;',
            getenv('WP_ROCKET_URL'),
            getenv('WP_ROCKET_KEY'),
            getenv('WP_ROCKET_EMAIL'),
        );
    }

    public function update(): ?Release
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

        return $this->createRelease($version, $downloadLink, '');
    }

    public function fetchZip(string $link): string
    {
        return Http::withUserAgent($this->userAgent())->get($link)->body();
    }
}
