<?php

namespace App\Recipes;

use App\Recipes\Exceptions\CouldNotAuthenticateException;
use App\Recipes\Exceptions\InvalidResponseStatusException;
use App\Recipes\Exceptions\NoActiveProductOrSubscriptionException;
use App\Recipes\Exceptions\NotRespondingException;
use App\Recipes\Exceptions\RateLimitReachedException;
use Filament\Forms;
use Illuminate\Support\Facades\Cache;

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
        ];
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        try {
            $this->getProductInformation();
        } catch (NoActiveProductOrSubscriptionException $e) {
            return 'No active product or subscription found.';
        } catch (InvalidResponseStatusException $e) {
            return 'Answer from remote server: '.$e->getMessage();
        } catch (CouldNotAuthenticateException $e) {
            return 'Answer from remote server: '.$e->getMessage();
        }

        return null;
    }

    /**
     * Fetch the package information.
     */
    public function doRequest(string $endpoint, string $method = 'GET', ?string $body = null)
    {
        $token = $this->package->getSecret('access_token');
        $secret = $this->package->getSecret('access_token_secret');

        $data = [
            'host' => parse_url($endpoint, PHP_URL_HOST),
            'request_uri' => parse_url($endpoint, PHP_URL_PATH),
            'method' => $method,
        ];

        if ($body) {
            $data['body'] = $body;
        }

        $signature = hash_hmac('sha256', json_encode($data), $secret);
        $requestUid = hash('sha256', json_encode($data).$token.$secret);

        $jsonResponse = Cache::remember($requestUid, 60, function () use ($token, $signature, $method, $endpoint, $body) {
            $request = $this->httpClient::withHeaders([
                "Authorization: Bearer {$token}",
                "X-Woo-Signature: {$signature}",
            ])->withQueryParameters([
                'token' => $token,
                'signature' => $signature,
            ]);

            if ($body) {
                $request->withBody($body);
            }

            return $request
                ->send($method, $endpoint)
                ->json();
        });

        return $jsonResponse;
    }

    private function getProductInformation()
    {
        $subscriptions = collect($this->doRequest(
            endpoint: 'https://woocommerce.com/wp-json/helper/1.0/subscriptions',
            method: 'GET',
        ));

        $authenticationIssues = [
            'not_found',
            'invalid_signature',
        ];
        if (in_array(($subscriptions['code'] ?? false), $authenticationIssues)) {
            throw new CouldNotAuthenticateException(($subscriptions['message'] ?? null));
        }

        if ($subscriptions->get('data') && $subscriptions->get('data')['status'] !== 200) {
            throw new InvalidResponseStatusException($this);
        }

        $subscription = $subscriptions
            ->mapWithKeys(fn ($subscription) => [$subscription['zip_slug'] => $subscription])
            ->get($this->package->settings['slug']);

        if (! $subscription) {
            throw new NoActiveProductOrSubscriptionException($this);
        }

        return $subscription;
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $subscription = $this->getProductInformation();
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
