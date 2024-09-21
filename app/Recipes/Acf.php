<?php

namespace App\Recipes;

use App\Recipes\Exceptions\LatestVersionFailedException;
use Filament\Forms;

class Acf extends Recipe
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
        return 'Advanced Custom Fields Pro';
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
        $data = [
            'acf_license' => $this->package->secrets()->get('license_key'),
            'wp_url' => $this->package->settings['source_url'],
            'p' => 'pro',
        ];

        $response = $this->request('v2/plugins/validate', $data);

        $status = $response['status'] ?? '';
        $message = $response['message'] ?? '';

        if ($status !== 1) {
            return $message;
        }

        return null;
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $version = $this->getLatestVersion();

        if (! $version) {
            throw new LatestVersionFailedException($this);
        }

        $downloadLink = sprintf(
            'https://connect.advancedcustomfields.com/v2/plugins/download?t=%s&p=pro&k=%s',
            $version,
            $this->package->secrets()->get('license_key'),
        );

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }

    private function request(string $endpoint, array $data = [])
    {
        $url = "https://connect.advancedcustomfields.com/$endpoint";
        $data = array_merge($data, []);

        $response = $this->httpClient::withQueryParameters($data)->post($url);

        return $response->json();
    }

    /**
     * Retrieve the latest version of the package.
     */
    private function getLatestVersion()
    {
        $packages = $this->httpClient::get('https://connect.advancedcustomfields.com/packages.json')->json();

        if (! is_array($packages) || ! isset($packages['packages']['wpengine/advanced-custom-fields-pro'])) {
            return null;
        }

        return array_key_first($packages['packages']['wpengine/advanced-custom-fields-pro']);
    }
}
