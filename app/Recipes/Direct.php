<?php

namespace App\Recipes;

use App\Recipes\Exceptions\InvalidResponseStatusException;
use Filament\Forms;

class Direct extends Recipe
{
    use Concerns\GetsVersionFromPlugin;

    // TODO: add secrets to use in url

    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'Direct';
    }

    /**
     * The form schema for the recipe.
     */
    public static function forms(): array
    {
        return [
            Forms\Components\TextInput::make('url')
                ->label('Url')
                ->required()
                ->helperText('The direct link to the package.'),
        ];
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        return null;
    }

    /**
     * Download the package using the JSON response.
     */
    private function getDownloadLinkFromJson($json): ?string
    {
        $packageDownloadLink = null;
        $possibleDownloadLinkKeys = [
            'download_link',
            'downloadLink',
            'download',
            'download_url',
            'url',
            'file',
            'package',
            'plugin',
            'theme',
        ];

        foreach ($possibleDownloadLinkKeys as $key) {
            if (isset($json[$key])) {
                $packageDownloadLink = $json[$key];
                break;
            }
        }

        if ($packageDownloadLink === null) {
            return null;
        }

        $downloadLink = $packageDownloadLink;

        return $downloadLink;
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $response = $this->httpClient::get($this->package->settings['url']);

        if (! $response->successful()) {
            throw new InvalidResponseStatusException($this);
        }

        if ($response->header('content-type') === 'application/json') {
            $downloadLink = $this->getDownloadLinkFromJson($response->json());
        } else {
            $downloadLink = $this->package->settings['url'];
        }

        return [
            'version' => $this->getVersionFromPlugin($downloadLink),
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }
}
