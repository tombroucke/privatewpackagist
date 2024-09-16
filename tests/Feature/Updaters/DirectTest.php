<?php

namespace Tests\Feature\Updaters;

use App\Models\Package;
use App\Updaters\Direct;
use Tests\TestCase;

class DirectTest extends TestCase
{
    private Direct $direct;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'js-composer',
            'updater' => 'direct',
            'settings' => [
                'url' => 'https://support.wpbakery.com/updates/download-link?product=vc&url=${{ JS_COMPOSER_SITE_URL }}&key=${{JS_COMPOSER_LICENSE_KEY}}',
            ],
        ]);

        $this->direct = $package->updater();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->direct->validationErrors()->all());

        putenv('JS_COMPOSER_SITE_URL');
        putenv('JS_COMPOSER_LICENSE_KEY');
        $this->assertContains('Env. variable JS_COMPOSER_SITE_URL is required', $this->direct->validationErrors()->all());
        $this->assertContains('Env. variable JS_COMPOSER_LICENSE_KEY is required', $this->direct->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Js Composer', $this->direct->fetchPackageTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->direct->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->direct->downloadLink());
        $this->assertTrue(filter_var($this->direct->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->direct->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->direct->testDownload());
    }
}
