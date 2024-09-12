<?php

namespace App\Updaters;

use App\Exceptions\WpmlProductNotFoundException;
use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Wpml implements Contracts\Updater
{
    use Concerns\CreatesRelease;
    use Concerns\ExtractsChangelog;

    const ENV_VARIABLES = [
    ];

    public function __construct(private Package $package) {}

    public function fetchTitle(): string
    {
        $product = $this->getProduct($this->package->slug);

        return strip_tags($product['name']) ?? Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        if (! env('WPML_USER_ID')) {
            $errors->push('WPML_USER_ID is required');
        }

        if (! env('WPML_LICENSE_KEY')) {
            $errors->push('WPML_LICENSE_KEY is required');
        }

        return $errors;
    }

    public function update(): ?Release
    {
        $product = $this->getProduct($this->package->slug);

        if (! $product) {
            throw new WpmlProductNotFoundException;
        }

        $version = $product['version'];
        $changelog = $this->extractLatestChangelog($product['changelog'] ?? '', '#### (\d+\.\d+\.\d+)(?:\s*\n\n)?(.*?)(?=\n\n#### \d+\.\d+\.\d+|$)');
        $downloadLink = sprintf(
            $product['url'].'&user_id=%s&subscription_key=%s',
            getenv('WPML_USER_ID'),
            getenv('WPML_LICENSE_KEY'),
        );

        ray($downloadLink);

        return $this->createRelease($version, $downloadLink, $changelog);
    }

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
}
