<?php

namespace App\Recipes;

use App\Recipes\Exceptions\NoActiveProductOrSubscriptionException;
use Filament\Forms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Wpml extends Recipe
{
    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [
        'user_id',
        'license_key',
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

            Forms\Components\TextInput::make('user_id')
                ->label('User ID')
                ->required(),

            Forms\Components\TextInput::make('license_key')
                ->required(),
        ];
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
        $response = Http::get('http://d2salfytceyqoe.cloudfront.net/wpml33-products.json');
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
