<?php

namespace Tests\Unit;

use App\Models\Package;
use App\PackageDownloader;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Unit\Recipe\TestRecipe;

class PackageDownloaderTest extends TestCase
{
    private TestRecipe $recipe;

    private Package $package;

    public function setUp(): void
    {
        parent::setUp();

        $this->package = new Package([
            'slug' => 'package-name',
            'name' => 'Package Name',
            'type' => 'wordpress-testplugin',
            'recipe' => 'recipe',
            'settings' => [
                'secrets' => [
                    'license_key' => Crypt::encryptString('test_license_key'),
                ],
            ],
        ]);
        $this->recipe = new TestRecipe($this->package, new Http);
    }

    public function test_download_can_be_stored(): void
    {
        Http::fake([
            'https://example.com/download' => Http::response(file_get_contents(__DIR__.'/stubs/test.zip')),
        ]);
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->assertEquals(
            'testplugin/package-name/package-name-1.0.0.zip',
            $packageDownloader->store($this->package->generateReleasePath('1.0.0'))
        );
    }

    public function test_download_can_not_be_stored(): void
    {
        Http::fake([
            'https://example.com/download' => Http::response(file_get_contents(__DIR__.'/stubs/test.zip')),
        ]);
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->expectException(\App\Exceptions\CouldNotDownloadPackageException::class);
        $packageDownloader->store(null);
    }

    public function test_download_can_be_tested(): void
    {
        Http::fake([
            'https://example.com/download' => Http::response(file_get_contents(__DIR__.'/stubs/test.zip')),
        ]);
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->assertTrue($packageDownloader->test());

    }

    public function test_test_can_fail(): void
    {
        Http::fake([
            'https://example.com/download' => Http::response(''),
        ]);
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->expectException(\Exception::class);

        $packageDownloader->test();
    }

    public function test_zip_can_be_validated(): void
    {
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->assertNotEmpty($packageDownloader->validateZip(file_get_contents(__DIR__.'/stubs/test.zip')));
    }

    public function test_zip_can_be_validated_empty(): void
    {
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->expectExceptionMessage('The file type \'none\' is invalid.');
        $packageDownloader->validateZip('');
    }

    public function test_zip_can_be_validated_with_invalid_text(): void
    {
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->expectExceptionMessage('This is not a zip, but some text');
        $packageDownloader->validateZip(file_get_contents(__DIR__.'/stubs/test.txt'));
    }

    public function test_zip_can_be_validated_incorrect_type(): void
    {
        $packageDownloader = new PackageDownloader($this->recipe, new Http);
        $this->expectExceptionMessage('The file type \'image/jpeg\' is invalid.');
        $packageDownloader->validateZip(file_get_contents(__DIR__.'/stubs/test.jpg'));
    }
}
