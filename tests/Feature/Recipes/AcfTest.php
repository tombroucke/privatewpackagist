<?php

namespace Tests\Feature\Recipes;

use App\Models\Package;
use App\Recipes\Acf;
use Tests\TestCase;

class AcfTest extends TestCase
{
    private Acf $acf;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'advanced-custom-fields-pro',
            'recipe' => 'acf',
            'settings' => [
                'url' => 'https://www.advancedcustomfields.com/',
            ],
        ]);

        $this->acf = $package->recipe();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->acf->validationErrors()->all());

        putenv('ACF_LICENSE_KEY');
        $this->assertContains('Env. variable ACF_LICENSE_KEY is required', $this->acf->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Advanced Custom Fields Pro', $this->acf->fetchPackageTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->acf->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->acf->downloadLink());
        $this->assertTrue(filter_var($this->acf->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->acf->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->acf->testDownload());
    }
}
