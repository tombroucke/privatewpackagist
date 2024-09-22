<?php

namespace App\Recipes;

use App\Recipes\Exceptions\NoActiveProductOrSubscriptionException;
use Filament\Forms;
use Illuminate\Support\Str;

class Wpml extends Recipe
{
    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [
        'user_id',
        'license_key',
        'site_key',
    ];

    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'WPML';
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

            Forms\Components\TextInput::make('user_id')
                ->label('User ID')
                ->required(),

            Forms\Components\TextInput::make('license_key')
                ->required(),

            Forms\Components\TextInput::make('site_key')
                ->label('Site key')
                ->required(),
        ];
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        $endpoint = 'https://api.wpml.org/';

        $args = [
            'action' => 'site_key_validation',
            'site_key' => $this->package->secrets()->get('site_key'),
            'site_url' => $this->package->settings['source_url'],
        ];

        $responseBody = $this->httpClient::asForm()
            ->post($endpoint, $args)
            ->body();

        $body = unserialize($responseBody);

        $active = property_exists($body, 'success');
        $message = property_exists($body, 'error') ? 'Answer from remote server: '.$body->error : 'Invalid license key.';

        return $active ? null : $message;
    }

    /**
     * The package title.
     */
    public function fetchPackageTitle(): string
    {
        $product = $this->getProduct($this->package->slug);

        return $product['name'] ?? Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->stripTags();
    }

    /**
     * Fetch the product information.
     */
    private function getProduct($slug)
    {
        $response = $this->httpClient::get('http://d2salfytceyqoe.cloudfront.net/wpml33-products.json');
        $body = $response->body();

        $products = json_decode($body, true);

        if (! is_array($products) || ! isset($products['downloads']['plugins'])) {
            return null;
        }

        return collect($products['downloads']['plugins'])->firstWhere('slug', $slug);
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        // TODO: fetch the package information with sitekey instead of license key
        $product = $this->getProduct($this->package->settings['slug']);

        if (! $product) {
            throw new NoActiveProductOrSubscriptionException($this);
        }

        $version = $product['version'];
        $changelog = $this->extractLatestChangelog($product['changelog'] ?? '', '#### (\d+\.\d+\.\d+)(?:\s*\n\n)?(.*?)(?=\n\n#### \d+\.\d+\.\d+|$)');
        $downloadLink = sprintf(
            $product['url'].'&user_id=%s&subscription_key=%s',
            $this->package->secrets()->get('user_id'),
            $this->package->secrets()->get('license_key'),
        );

        return [
            'version' => $version,
            'changelog' => $changelog,
            'downloadLink' => $downloadLink,
        ];
    }
}
