<?php

namespace App\Updaters;

use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Woocommerce implements Contracts\Updater
{
    use Concerns\ExtractsChangelog;
    use Concerns\StoresDownload;

    const ENV_VARIABLES = [
    ];

    public function __construct(private Package $package) {}

    public function fetchTitle(): string
    {
        return Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        if (! env('WOOCOMMERCE_ACCESS_TOKEN')) {
            $errors->push('WOOCOMMERCE_ACCESS_TOKEN is required');
        }

        if (! env('WOOCOMMERCE_ACCESS_TOKEN_SECRET')) {
            $errors->push('WOOCOMMERCE_ACCESS_TOKEN_SECRET is required');
        }

        return $errors;
    }

    public function createRelease(): ?Release
    {

        $subscriptions = $this->doRequest(
            endpoint: 'https://woocommerce.com/wp-json/helper/1.0/subscriptions',
            method: 'GET',
        );

        $subscription = array_reduce(
            (array) $subscriptions,
            fn ($carry, $subscription) => $subscription->zip_slug === $this->package->slug ? $subscription : $carry,
            null
        );

        if (! $subscription) {
            echo 'Cannot find subscription'.PHP_EOL;
            exit(1);
        }

        $productId = $subscription->product_id;

        $payload = [
            $productId => [
                'product_id' => $productId,
                'file_id' => '',
            ],
        ];

        $body = json_encode([
            'products' => $payload,
        ]);

        $response = $this->doRequest(
            endpoint: 'https://woocommerce.com/wp-json/helper/1.0/update-check',
            method: 'POST',
            body: $body,
        );

        $product = $response->{$productId};

        if (! $product) {
            return null;
        }

        $version = $product->version;
        $changelog = '';
        $downloadLink = $product->package;

        $existingRelease = $this->package->releases()->where('version', $version)->first();
        if ($existingRelease) {
            return $existingRelease;
        }

        $filePath = $this->storeDownload($this->package, $downloadLink, $version);

        return $this->package->releases()->create([
            'version' => $version,
            'changelog' => $changelog,
            'path' => $filePath,
        ]);
    }

    public function doRequest(string $endpoint, string $method = 'GET', ?string $body = null)
    {
        $accessToken = getenv('WOOCOMMERCE_ACCESS_TOKEN');
        $accessTokenSecret = getenv('WOOCOMMERCE_ACCESS_TOKEN_SECRET');

        $data = [
            'host' => parse_url($endpoint, PHP_URL_HOST),
            'request_uri' => parse_url($endpoint, PHP_URL_PATH),
            'method' => $method,
        ];

        if ($body) {
            $data['body'] = $body;
        }

        $signature = hash_hmac('sha256', json_encode($data), $accessTokenSecret);
        $query = http_build_query(['token' => $accessToken, 'signature' => $signature]);
        $response = exec(sprintf(
            'curl -s -X %s %s -H %s -H %s %s',
            $method,
            $body ? '--data '.escapeshellarg($body) : '',
            escapeshellarg('Authorization: Bearer '.$accessToken),
            escapeshellarg('X-Woo-Signature: '.$signature),
            escapeshellarg($endpoint.'?'.$query),
        ));

        return json_decode($response);
    }
}
