<?php

namespace Tests\Feature\Recipes;

use App\Models\Package;
use App\Recipes\Manual;
use Tests\TestCase;

class ManualTest extends TestCase
{
    private Manual $manual;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'chauffeur-booking-system',
            'recipe' => 'manual',
            'settings' => [],
        ]);

        $this->manual = $package->recipe();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->manual->validationErrors()->all());
    }

    public function test_update_throws_exception(): void
    {
        $this->expectException(\App\Exceptions\ManualRecipeCanNotUpdatePackages::class);
        $this->manual->update();
    }

    public function test_user_agent_is_set(): void
    {
        $userAgent = config('packagist.user_agent');
        $this->assertEquals($userAgent, $this->manual->userAgent());
    }
}
