<?php

namespace App\Recipes;

use Filament\Forms;

class ElementorPro extends Recipe
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
        return 'Elementor Pro';
    }

    /**
     * The form schema for the recipe.
     */
    public static function forms(): array
    {
        return [
            Forms\Components\TextInput::make('source_url')
                ->label('Source URL')
                ->url()
                ->required(),

            Forms\Components\TextInput::make('license_key')
                ->required(),
        ];
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        $args = [
            'license' => $this->package->secrets()->get('license_key'),
            'item_name' => 'Elementor Pro',
            'url' => $this->package->settings['source_url'],
        ];

        $response = $this->doRequest('license/validate', $args);

        $active = strtolower($response['status'] ?? '') === 'active';
        $message = match ($response['error'] ?? '') {
            'missing' => 'License key is missing or invalid.',
            'site_inactive' => 'License key is not active for this site.',
            default => 'License key is invalid.',
        };

        return $active ? null : $message;
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

    public function doRequest(string $endpoint, array $data = []): array
    {
        $baseUrl = 'https://my.elementor.com/api/v2/';
        $response = $this->httpClient::withUserAgent($this->userAgent())->post($baseUrl.$endpoint, $data);

        return $response->json();
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $args = [
            'license' => $this->package->secrets()->get('license_key'),
            'name' => 'Elementor Pro',
            'url' => $this->package->settings['source_url'],
        ];
        $response = $this->doRequest('pro/info', $args);

        $sections = unserialize($response['sections']);

        $changelog = ($sections['changelog'] ?? ''); // Needs to be regex matched

        return [
            'version' => $response['new_version'],
            'changelog' => '',
            'downloadLink' => $response['download_link'],
        ];
    }
}
