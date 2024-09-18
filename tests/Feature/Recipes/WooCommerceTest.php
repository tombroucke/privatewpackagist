<?php

namespace Tests\Feature\Recipes;

use App\Models\Package;
use App\Recipes\Woocommerce;
use Tests\TestCase;

class WooCommerceTest extends TestCase
{
    private Woocommerce $woocommerce;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'woocommerce-product-filters',
            'recipe' => 'woocommerce',
            'settings' => [
                'slug' => 'woocommerce-product-filters',
            ],
        ]);

        $this->woocommerce = $package->recipe();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->woocommerce->validationErrors()->all());

        putenv('WOOCOMMERCE_ACCESS_TOKEN');
        $this->assertContains('Env. variable WOOCOMMERCE_ACCESS_TOKEN is required', $this->woocommerce->validationErrors()->all());

        putenv('WOOCOMMERCE_ACCESS_TOKEN_SECRET');
        $this->assertContains('Env. variable WOOCOMMERCE_ACCESS_TOKEN_SECRET is required', $this->woocommerce->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Woocommerce Product Filters', $this->woocommerce->fetchPackageTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->woocommerce->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->woocommerce->downloadLink());
        $this->assertTrue(filter_var($this->woocommerce->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->woocommerce->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->woocommerce->testDownload());
    }
}
