<?php

namespace Tests\Feature\Recipes;

use App\Models\Package;
use App\Recipes\Edd;
use Tests\TestCase;

class EddTest extends TestCase
{
    private Edd $edd;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'woocommerce-pdf-ips-pro',
            'recipe' => 'edd',
            'settings' => [
                'slug' => 'PDF Invoices & Packing Slips for WooCommerce - Professional',
                'source_url' => 'https://github.tombroucke.be',
                'endpoint_url' => 'https://wpovernight.com/license-api',
                'method' => 'GET',
            ],
        ]);

        $this->edd = $package->recipe();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->edd->validationErrors()->all());

        putenv('WOOCOMMERCE_PDF_IPS_PRO_LICENSE_KEY');
        $this->assertNotEmpty($this->edd->validationErrors()->all());
        $this->assertContains('Invalid license', $this->edd->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('PDF Invoices & Packing Slips for WooCommerce - Professional', $this->edd->fetchPackageTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->edd->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->edd->downloadLink());
        $this->assertTrue(filter_var($this->edd->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->edd->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->edd->testDownload());
    }
}
