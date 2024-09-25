<?php

namespace App\Recipes;

use App\Recipes\Exceptions\NoDownloadLinkException;
use Filament\Forms;

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
        ];
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        $packageInformation = $this->doRequest('updatecheck');

        $valid = ($packageInformation['download_url'] ?? '') !== '';

        $messageKeys = [
            'upgrade_warning_notice',
            'error',
        ];

        foreach ($messageKeys as $key) {
            if (isset($packageInformation[$key])) {
                $message = 'Answer from remote server: '.$packageInformation[$key];
                break;
            }
        }

        return $valid ? null : $message ?? 'License key is not valid';
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
     * Handle the request.
     */
    public function doRequest(string $action)
    {
        $response = $this->httpClient::withUserAgent($this->userAgent())->get($this->package->settings['meta_data_url'], [
            'wpaction' => $action,
            'dlid' => $this->package->getSecret('license_key'),
            'wpslug' => $this->package->settings['slug'],
        ]);

        return $response->json();
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $packageInformation = $this->doRequest('updatecheck');

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
