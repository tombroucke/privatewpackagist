<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Token;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagesJsonTest extends TestCase
{
    use RefreshDatabase;

    private string $basicAuthUsername;

    private string $basicAuthToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->basicAuthUsername = 'tom';
        $this->basicAuthToken = bin2hex(random_bytes(16));

        config(['packagist.vendor' => 'privatewpackagist']);

        Token::create([
            'username' => $this->basicAuthUsername,
            'token' => $this->basicAuthToken,
        ]);

        $package = Package::create([
            'name' => 'WooCommerce Product Filters',
            'slug' => 'woocommerce-product-filters',
            'type' => 'wordpress-plugin',
            'recipe' => 'woocommerce',
            'settings' => [
                'slug' => 'woocommerce-product-filters',
            ],
        ]);

        $package->releases()->create([
            'version' => '1.4.32',
            'changelog' => 'Initial release',
            'path' => 'plugin/woocommerce-product-filters/woocommerce-product-filters-1.4.32.zip',
        ]);

        $package->releases()->create([
            'version' => '1.4.33',
            'changelog' => 'Updated release',
            'path' => 'plugin/woocommerce-product-filters/woocommerce-product-filters-1.4.33.zip',
        ]);
    }

    public function test_packages_json_is_protected_by_basic_auth(): void
    {
        $response = $this->get('/repo/packages.json');

        $response->assertStatus(401);
    }

    public function test_packages_json_can_be_fetched(): void
    {

        $response = $this->authenticatedRequest('/repo/packages.json');

        $response->assertStatus(200);
    }

    public function test_package_is_valid_json(): void
    {
        $response = $this->authenticatedRequest('/repo/packages.json');

        $PackagesCache = $response->json();

        $this->assertCount(1, $PackagesCache['packages']);
        $this->assertCount(2, $PackagesCache['packages']['privatewpackagist-plugin/woocommerce-product-filters']);

        $response->assertJsonStructure([
            'packages' => [
                '*' => [
                    '*' => [
                        'name',
                        'version',
                        'type',
                        'require' => [
                            'composer/installers',
                        ],
                        'dist' => [
                            'url',
                            'type',
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function authenticatedRequest($path)
    {
        $basicAuth = base64_encode($this->basicAuthUsername.':'.$this->basicAuthToken);

        return $this->withHeader('Authorization', 'Basic '.$basicAuth)
            ->get($path);
    }
}
