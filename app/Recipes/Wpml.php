<?php

namespace App\Recipes;

use Filament\Forms;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Recipes\Exceptions\NoActiveProductOrSubscriptionException;

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
            Forms\Components\Select::make('slug')
                ->label('Slug')
                ->options(function () {
                    return static::plugins(new Http)
                        ->mapWithKeys(fn ($product, $key) => [$key => strip_tags($product['name'])]);
                })
                ->required(),

            Forms\Components\TextInput::make('source_url')
                ->label('Source URL')
                ->url()
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
            'site_key' => $this->package->getSecret('site_key'),
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

    public static function plugins($httpClient) : ?Collection
    {
        $plugins = Cache::remember('wpml_plugins', now()->addHours(6), function () use ($httpClient) {
            $response = $httpClient::get('http://d2salfytceyqoe.cloudfront.net/wpml33-products.json');
            $body = $response->body();

            $products = json_decode($body, true);

            return is_array($products) ? $products['downloads']['plugins'] ?? [] : [];
        });

        return collect($plugins);
    }

    /**
     * Fetch the product information.
     */
    private function getProduct($slug)
    {
        $plugins = $this->plugins($this->httpClient);

        if (!$plugins) {
            return null;
        }

        return $plugins->firstWhere('slug', $slug);
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
            $this->package->getSecret('user_id'),
            $this->package->getSecret('license_key'),
        );

        return [
            'version' => $version,
            'changelog' => $changelog,
            'downloadLink' => $downloadLink,
        ];
    }
}
