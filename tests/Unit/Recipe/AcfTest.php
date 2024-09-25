<?php

namespace Tests\Unit\Recipes;

use App\Models\Package;
use App\Recipes\Acf;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcfTest extends TestCase
{
    private Acf $acf;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('secrets', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('value');
            $table->timestamps();
        });

        Schema::create('package_secret', function ($table) {
            $table->id();
            $table->foreignId('package_id')->constrained();
            $table->foreignId('secret_id')->constrained();
            $table->timestamps();
        });

        Schema::create('packages', function ($table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->string('type');
            $table->string('recipe');
            $table->json('settings');
            $table->timestamps();
        });

        DB::table('secrets')->insert([
            'name' => 'ACF License Key',
            'type' => 'license_key',
            'value' => Crypt::encryptString('test_license_key'),
        ]);

        DB::table('packages')->insert([
            'slug' => 'advanced-custom-fields-pro',
            'name' => 'Temp Name',
            'type' => 'wordpress-testplugin',
            'recipe' => 'acf',
            'settings' => json_encode([
                'secrets' => [
                    'license_key' => Crypt::encryptString('test_license_key'),
                ],
            ]),
        ]);

        DB::table('package_secret')->insert([
            'package_id' => 1,
            'secret_id' => 1,
        ]);

        $this->acf = Package::find(1)->recipe();

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
