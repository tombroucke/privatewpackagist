<?php

namespace App\Recipes;

use App\Recipes\Exceptions\InvalidResponseStatusException;
use Filament\Forms;

class GravityForms extends Recipe
{
    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [
        'license_key',
    ];

    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'Gravity Forms';
    }

    /**
     * The form schema for the recipe.
     */
    public static function forms(): array
    {
        return [
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required(),

            Forms\Components\TextInput::make('license_key')
                ->required(),
        ];
    }

    /**
     * The package information.
     */
    protected function fetchPackageInformation(): array
    {
        $url = sprintf(
            'https://gravityapi.com/wp-content/plugins/gravitymanager/api.php?op=get_plugin&slug=%s&key=%s',
            $this->package->settings['slug'],
            $this->package->secrets()->get('license_key')
        );

        $response = $this->httpClient::get($url);

        if ($response->status() !== 200) {
            throw new InvalidResponseStatusException($this);
        }

        $body = $response->body();
        $packageInformation = unserialize($body);

        $version = $packageInformation['version'];
        $downloadLink = $packageInformation['download_url_latest'];
        $changelog = $this->extractLatestChangelog($packageInformation['changelog'], 'Gravity Forms v[\d.]+ Changelog\s*-+\s*((?:-.*\n)+)');

        return [
            'version' => $version,
            'changelog' => $changelog,
            'downloadLink' => $downloadLink,
        ];
    }
}
