<?php

namespace Tests\Unit\Recipes;

use App\Models\Package;
use App\Recipes\Acf;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
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
                'secrets' => [
                    'license_key' => Crypt::encryptString('test_license_key'),
                ],
            ],
        ]);

        $this->acf = $package->recipe();

        Http::fake([
            'https://connect.advancedcustomfields.com/packages.json' => Http::response([
                'packages' => [
                    'wpengine/advanced-custom-fields-pro' => [
                        '6.3.5' => [],
                    ],
                ],
            ]),
        ]);
    }

    public function test_package_version_is_fetched(): void
    {
        $this->assertEquals('6.3.5', $this->acf->version());
    }

    public function test_package_download_link_is_generated(): void
    {

        $this->assertEquals(
            'https://connect.advancedcustomfields.com/v2/plugins/download?t=6.3.5&p=pro&k=test_license_key',
            $this->acf->downloadLink(),
        );
    }

    public function test_package_changelog_is_empty(): void
    {
        $this->assertEquals('', $this->acf->changelog());
    }
}
