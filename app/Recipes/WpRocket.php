<?php

namespace App\Recipes;

use App\Recipes\Exceptions\InvalidResponseStatusException;
use App\Recipes\Exceptions\UnexpectedResponseException;
use Filament\Forms;
use Illuminate\Support\Facades\Http;

class WpRocket extends Recipe
{
    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [
        'license_key',
        'license_email',
        'license_url',
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
            Forms\Components\TextInput::make('license_key')
                ->required(),

            Forms\Components\TextInput::make('license_email')
                ->label('License Email')
                ->required(),

            Forms\Components\TextInput::make('license_url')
                ->label('License URL')
                ->required(),
        ];
    }

    /**
     * The user agent for the request.
     */
    public function userAgent(): string
    {
        return sprintf('%1$s; %2$s;WP-Rocket|3.6.3|%3$s|%4$s|%2$s|8.2;',
            config('packagist.user_agent'),
            $this->package->secrets()->get('license_url'),
            $this->package->secrets()->get('license_key'),
            $this->package->secrets()->get('license_email'),
        );
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $response = Http::withUserAgent($this->userAgent())->get('https://api.wp-rocket.me/check_update.php');
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
            $this->package->secrets()->get('license_key'),
            $version,
        );

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }
}
