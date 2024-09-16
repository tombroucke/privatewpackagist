<?php

namespace App\Updaters;

use App\Exceptions\UnexpectedResponseException;
use App\Exceptions\WpRocketInvalidRequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WpRocket extends Abstracts\Updater implements Contracts\Updater
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

        if (! getenv('WP_ROCKET_KEY') !== false) {
            $errors->push('Env. variable WP_ROCKET_KEY is required');
        }

        if (! getenv('WP_ROCKET_EMAIL') !== false) {
            $errors->push('Env. variable WP_ROCKET_EMAIL is required');
        }

        if (! getenv('WP_ROCKET_URL') !== false) {
            $errors->push('Env. variable WP_ROCKET_URL is required');
        }

        return $errors;
    }

    public function userAgent(): string
    {
        return sprintf('%1$s; %2$s;WP-Rocket|3.6.3|%3$s|%4$s|%2$s|8.2;',
            config('app.wp_user_agent'),
            getenv('WP_ROCKET_URL'),
            getenv('WP_ROCKET_KEY'),
            getenv('WP_ROCKET_EMAIL'),
        );
    }

    protected function fetchPackageInformation(): array
    {
        $response = Http::withUserAgent($this->userAgent())->get('https://api.wp-rocket.me/check_update.php');
        $body = $response->body();

        $jsonResponse = $response->json();

        if (is_array($jsonResponse) && $jsonResponse['success'] === false) {
            throw new WpRocketInvalidRequestException($jsonResponse['data']['reason']);
        }

        if (! preg_match('@^(?<stable_version>\d+(?:\.\d+){1,3}[^|]*)\|(?<package>(?:http.+\.zip)?)\|(?<user_version>\d+(?:\.\d+){1,3}[^|]*)(?:\|+)?$@', $body, $match)) {
            throw new UnexpectedResponseException('WP Rocket response does not match expected format');
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
}
