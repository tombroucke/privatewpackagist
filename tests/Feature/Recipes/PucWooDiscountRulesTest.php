<?php

namespace Tests\Feature\Recipes;

use App\Models\Package;
use App\Recipes\Puc;
use Tests\TestCase;

class PucWooDiscountRulesTest extends TestCase
{
    private Puc $puc;

    private $sourceUrl = 'https://tombroucke.be';

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'woo-discount-rules-pro',
            'recipe' => 'puc',
            'settings' => [
                'slug' => 'discount-rules-v2-pro',
                'source_url' => $this->sourceUrl,
                'meta_data_url' => 'https://my.flycart.org?wpaction=updatecheck&wpslug=discount-rules-v2-pro&dlid=${{ WOO_DISCOUNT_RULES_PRO_LICENSE_KEY }}',
            ],
        ]);

        $this->puc = $package->recipe();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->puc->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Woo Discount Rules Pro', $this->puc->fetchPackageTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->puc->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->puc->downloadLink());
        $this->assertTrue(filter_var($this->puc->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->puc->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->puc->testDownload());
    }

    public function test_user_agent_is_set(): void
    {
        $userAgent = config('packagist.user_agent').'; '.$this->sourceUrl;
        $this->assertEquals($userAgent, $this->puc->userAgent());
    }
}
