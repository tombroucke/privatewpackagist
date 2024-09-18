<?php

namespace Tests\Feature\Recipes;

use App\Models\Package;
use App\Recipes\GravityForms;
use Tests\TestCase;

class GravityFormsTest extends TestCase
{
    private GravityForms $gravityForms;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'gravityformszapier',
            'recipe' => 'gravityforms',
            'settings' => [
                'slug' => 'gravityformszapier',
            ],
        ]);

        $this->gravityForms = $package->recipe();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->gravityForms->validationErrors()->all());

        putenv('GRAVITYFORMS_LICENSE_KEY');
        $this->assertContains('Env. variable GRAVITYFORMS_LICENSE_KEY is required', $this->gravityForms->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Gravityformszapier', $this->gravityForms->fetchPackageTitle());
    }

    public function test_version_is_set(): void
    {
        $this->assertNotNull($this->gravityForms->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->assertNotNull($this->gravityForms->downloadLink());
        $this->assertTrue(filter_var($this->gravityForms->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->assertNotNull($this->gravityForms->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->gravityForms->testDownload());
    }
}
