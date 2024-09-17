<?php

namespace App\Recipes;

use App\Recipes\Exceptions\InvalidResponseStatusException;
use App\Recipes\Exceptions\NoActiveProductOrSubscriptionException;
use App\Recipes\Exceptions\NotRespondingException;
use App\Recipes\Exceptions\RateLimitReachedException;
use Filament\Forms;
use Illuminate\Support\Facades\Http;

class WooCommerce extends Recipe
{
    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [
        'access_token',
        'access_token_secret',
    ];

    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'WooCommerce';
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

            Forms\Components\TextInput::make('access_token')
                ->label('Access Token')
                ->required(),

            Forms\Components\TextInput::make('access_token_secret')
                ->label('Access Token Secret')
                ->required(),
        ];
    }

    /**
     * Fetch the package information.
     */
    public function doRequest(string $endpoint, string $method = 'GET', ?string $body = null)
    {
        $token = $this->package->secrets()->get('access_token');
        $secret = $this->package->secrets()->get('access_token_secret');

        $data = [
            'host' => parse_url($endpoint, PHP_URL_HOST),
            'request_uri' => parse_url($endpoint, PHP_URL_PATH),
            'method' => $method,
        ];

        if ($body) {
            $data['body'] = $body;
        }

        $signature = hash_hmac('sha256', json_encode($data), $secret);

        $request = Http::withHeaders([
            "Authorization: Bearer {$token}",
            "X-Woo-Signature: {$signature}",
        ])->withQueryParameters([
            'token' => $token,
            'signature' => $signature,
        ]);

        if ($body) {
            $request->withBody($body);
        }

        $response = $request->send($method, $url);

        return json_decode($response, true);
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $subscriptions = collect($this->doRequest(
            endpoint: 'https://woocommerce.com/wp-json/helper/1.0/subscriptions',
            method: 'GET',
        ));

        if ($subscriptions->get('data') && $subscriptions->get('data')['status'] !== 200) {
            throw new InvalidResponseStatusException($this);
        }

        $subscription = $subscriptions
            ->mapWithKeys(fn ($subscription) => [$subscription['zip_slug'] => $subscription])
            ->get($this->package->slug);

        if (! $subscription) {
            throw new NoActiveProductOrSubscriptionException($this);
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
                throw new NotRespondingException($this);
            }

            $response = $this->doRequest(
                endpoint: 'https://woocommerce.com/wp-json/helper/1.0/update-check',
                method: 'POST',
                body: $body,
            );

            $limitReached = ($response['code'] ?? false) === 'wccom_rest_limit_reached';

            if ($limitReached) {
                throw new RateLimitReachedException($this);
            }

            $status = $response['data']['status'] ?? 200;
            if ($status !== 200) {
                sleep(3);
            }
        }

        $product = $response[$productId];

        if (! $product) {
            throw new NoActiveProductOrSubscriptionException($this);
        }

        return [
            'version' => $product['version'],
            'changelog' => '',
            'downloadLink' => $product['package'],
        ];
    }
}
