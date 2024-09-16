<?php

namespace Tests\Feature\Updaters;

use App\Models\Package;
use App\Updaters\AdminColumnsPro;
use Tests\TestCase;

class AdminColumnsProTest extends TestCase
{
    private AdminColumnsPro $adminColumnsPro;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'admin-columns-pro',
            'updater' => 'admin_columns_pro',
            'settings' => [
                'slug' => 'admin-columns-pro',
            ],
        ]);

        $this->adminColumnsPro = $package->updater();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->adminColumnsPro->validationErrors()->all());

        putenv('ADMIN_COLUMNS_PRO_LICENSE_KEY');
        $this->assertContains('Env. variable ADMIN_COLUMNS_PRO_LICENSE_KEY is required', $this->adminColumnsPro->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Admin Columns Pro', $this->adminColumnsPro->fetchTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->adminColumnsPro->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->adminColumnsPro->downloadLink());
        $this->assertTrue(filter_var($this->adminColumnsPro->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->adminColumnsPro->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->adminColumnsPro->testDownload());
    }
}
