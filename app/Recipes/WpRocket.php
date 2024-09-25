<?php

namespace App\Recipes;

use App\Recipes\Exceptions\InvalidResponseStatusException;
use App\Recipes\Exceptions\UnexpectedResponseException;
use Filament\Forms;

class WpRocket extends Recipe
{
    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [
        'license_key',
        'license_email',
    ];

    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'WP Rocket';
    }

    /**
     * The form schema for the recipe.
     */
    public static function forms(): array
    {
        return [
            Forms\Components\TextInput::make('license_url')
                ->label('License URL')
                ->url()
                ->required(),
        ];
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        $json = $this->httpClient::withUserAgent($this->userAgent())
            ->get('https://api.wp-rocket.me/valid_key.php')
            ->json();

        $active = ($json['success'] ?? false) === true;
        $message = match ($json['data']['reason'] ?? '') {
            'BAD_LICENSE' => 'Your license is not valid.',
            'BAD_NUMBER' => 'You have added as many sites as your current license allows.',
            'BAD_SITE' => 'This website is not allowed.',
            'BAD_KEY' => 'This license key is not recognized.',
            default => 'License key is not valid for this site',
        };

        return $active ? null : $message;
    }

    /**
     * The user agent for the request.
     */
    public function userAgent(): string
    {
        return sprintf('%1$s; %2$s;WP-Rocket|3.6.3|%3$s|%4$s|%2$s|8.2;',
            config('packagist.user_agent'),
            $this->package->settings['license_url'],
            $this->package->getSecret('license_key'),
            $this->package->getSecret('license_email'),
        );
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        sleep(1); // Prevent rate limiting. On package create/save, this is called immediately after the license key check.
        $response = $this->httpClient::withUserAgent($this->userAgent())->get('https://api.wp-rocket.me/check_update.php');
        $body = $response->body();

        $jsonResponse = $response->json();

        if (is_array($jsonResponse) && $jsonResponse['success'] === false) {
            throw new InvalidResponseStatusException($this);
        }

        if (! preg_match('@^(?<stable_version>\d+(?:\.\d+){1,3}[^|]*)\|(?<package>(?:http.+\.zip)?)\|(?<user_version>\d+(?:\.\d+){1,3}[^|]*)(?:\|+)?$@', $body, $match)) {
            throw new UnexpectedResponseException($this);
        }

        $version = $match['user_version'];

        $downloadLink = sprintf(
            'https://api.wp-rocket.me/%s/wp-rocket_%s.zip',
            $this->package->getSecret('license_key'),
            $version,
        );

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }
}
