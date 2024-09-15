<?php

namespace Tests\Feature\Updaters;

use App\Models\Package;
use App\Updaters\Wpml;
use Tests\TestCase;

class WpmlTest extends TestCase
{
    private Wpml $wpml;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'acfml',
            'updater' => 'wpml',
            'settings' => [
                'slug' => 'acfml',
            ],
        ]);

        $this->wpml = $package->updater();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->wpml->validationErrors()->all());

        putenv('WPML_USER_ID');
        $this->assertContains('Env. variable WPML_USER_ID is required', $this->wpml->validationErrors()->all());

        putenv('WPML_LICENSE_KEY');
        $this->assertContains('Env. variable WPML_LICENSE_KEY is required', $this->wpml->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Advanced Custom Fields Multilingual', $this->wpml->fetchTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->wpml->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->wpml->downloadLink());
        $this->assertTrue(filter_var($this->wpml->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->wpml->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->wpml->testDownload());
    }
}
