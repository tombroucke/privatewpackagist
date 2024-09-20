<?php

namespace Tests\Unit\Recipe;

use App\Models\Package;
use App\Recipes\Recipe as AbstractRecipe;
use Filament\Forms;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    private $recipe;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('packages', function ($table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->string('type');
            $table->string('recipe');
            $table->json('settings');
            $table->timestamps();
        });

        Schema::create('releases', function ($table) {
            $table->id();
            $table->foreignId('package_id')->constrained();
            $table->text('changelog');
            $table->string('version');
            $table->string('path');
            $table->timestamps();
        });

        DB::table('packages')->insert([
            'slug' => 'package-name',
            'name' => 'Package Name',
            'type' => 'wordpress-testplugin',
            'recipe' => 'recipe',
            'settings' => json_encode([
                'secrets' => [
                    'license_key' => Crypt::encryptString('test_license_key'),
                ],
            ]),
        ]);

        $package = Package::first();

        Http::fake([
            'https://example.com/download' => Http::response(file_get_contents(__DIR__.'/../stubs/test.zip')),
        ]);

        $this->recipe = new TestRecipe($package, new Http);
    }

    public function test_recipe_is_recipe_instance(): void
    {
        $this->assertInstanceOf(AbstractRecipe::class, $this->recipe);
    }

    public function test_slug_is_correct(): void
    {
        $this->assertEquals('test_recipe', $this->recipe::slug());
    }

    public function test_fetch_package_title_is_correct(): void
    {
        $this->assertEquals('Package Name', $this->recipe->fetchPackageTitle());
    }

    public function test_secrets_are_set(): void
    {
        $this->assertEquals(['license_key'], $this->recipe->secrets());
    }

    public function test_forms_is_set_and_contains_license_key_input(): void
    {
        $forms = $this->recipe::forms();

        $this->assertIsArray($forms);
        $this->assertCount(1, $forms);
        $this->assertInstanceOf(Forms\Components\TextInput::class, $forms[0]);
        $this->assertEquals('license_key', $forms[0]->getName());
    }

    public function test_release_can_be_created(): void
    {
        $release = $this->recipe->update();

        $this->assertInstanceOf(\App\Models\Release::class, $release);

        $this->assertEquals('1.0.0', $release->version);
        $this->assertEquals('testplugin/package-name/package-name-1.0.0.zip', $release->path);
        $this->assertFileExists(storage_path('app/packages/'.$release->path));

        // clean up
        unlink(storage_path('app/packages/'.$release->path));
        $dir = pathinfo($release->path, PATHINFO_DIRNAME);
        rmdir(storage_path('app/packages/'.$dir));
    }
}
