<?php

namespace App\Updaters;

use App\Exceptions\WoocommerceApiNotRespondingException;
use App\Exceptions\WoocommerceApiRestLimitReachedException;
use App\Exceptions\WoocommerceProductNotFoundException;
use App\Exceptions\WoocommerceSubscriptionNotFoundException;
use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Woocommerce implements Contracts\Updater
{
    use Concerns\CreatesRelease;
    use Concerns\ExtractsChangelog;

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

    public function update(): ?Release
    {

        $subscriptions = collect($this->doRequest(
            endpoint: 'https://woocommerce.com/wp-json/helper/1.0/subscriptions',
            method: 'GET',
        ))
            ->mapWithKeys(fn ($subscription) => [$subscription['zip_slug'] => $subscription]);

        $subscription = $subscriptions->get($this->package->slug);

        if (! $subscription) {
            throw new WoocommerceSubscriptionNotFoundException;
        }

        $productId = (int) $subscription['product_id'];

        $payload = [
            $productId => [
                'product_id' => $productId,
                'file_id' => '',
            ],
        ];

        $body = json_encode([
            'products' => $payload,
        ]);

        $status = null;
        $iterations = 0;
        while ($status !== 200) {
            if ($iterations++ > 5) {
                throw new WoocommerceApiNotRespondingException;
            }
            $response = $this->doRequest(
                endpoint: 'https://woocommerce.com/wp-json/helper/1.0/update-check',
                method: 'POST',
                body: $body,
            );

            $limitReached = ($response['code'] ?? false) === 'wccom_rest_limit_reached';
            if ($limitReached) {
                throw new WoocommerceApiRestLimitReachedException;
            }

            $status = $response['data']['status'] ?? 200;
            if ($status !== 200) {
                sleep(3);
            }
        }

        $product = $response[$productId];
        if (! $product) {
            throw new WoocommerceProductNotFoundException;
        }

        $version = $product['version'];
        $changelog = '';
        $downloadLink = $product['package'];

        return $this->createRelease($version, $downloadLink, $changelog);
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

        return json_decode($response, true);
    }
}
