<?php

namespace App\Recipes;

use App\Recipes\Exceptions\NoDownloadLinkException;
use Filament\Forms;
use Illuminate\Support\Facades\Http;

class Puc extends Recipe
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
        return 'YahnisElsts Plugin Update Checker';
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

            Forms\Components\TextInput::make('source_url')
                ->label('Source URL')
                ->url()
                ->required(),

            Forms\Components\TextInput::make('meta_data_url')
                ->label('Metadata URL')
                ->required(),

            Forms\Components\TextInput::make('license_key')
                ->required(),
        ];
    }

    /**
     * The user agent for the request.
     */
    public function userAgent(): string
    {
        return sprintf('%s; %s',
            config('packagist.user_agent'),
            $this->package->settings['source_url'],
        );
    }

    /**
     * The license key.
     */
    public function licenseKey(): string
    {
        return $this->package->secrets()->get('license_key');
    }

    /**
     * Handle the request.
     */
    public function doWpAction(string $action)
    {
        $response = Http::withUserAgent($this->userAgent())->get($this->package->settings['meta_data_url'], [
            'wpaction' => $action,
            'dlid' => $this->licenseKey(),
            'wpslug' => $this->package->settings['slug'],
        ]);

        return $response->json();
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $packageInformation = $this->doWpAction('updatecheck');

        if (! isset($packageInformation['download_url']) || $packageInformation === '') {
            throw new NoDownloadLinkException($this);
        }

        $version = $packageInformation['version'];
        $downloadLink = $packageInformation['download_url'];

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }
}
