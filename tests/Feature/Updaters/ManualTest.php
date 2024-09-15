<?php

namespace Tests\Feature\Updaters;

use App\Models\Package;
use App\Updaters\Manual;
use Tests\TestCase;

class ManualTest extends TestCase
{
    private Manual $manual;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'chauffeur-booking-system',
            'updater' => 'manual',
            'settings' => [],
        ]);

        $this->manual = $package->updater();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->manual->validationErrors()->all());
    }

    public function test_update_throws_exception(): void
    {
        $this->expectException(\App\Exceptions\ManualUpdaterCanNotUpdatePackages::class);
        $this->manual->update();
    }

    public function test_user_agent_is_set(): void
    {
        $userAgent = config('app.wp_user_agent');
        $this->assertEquals($userAgent, $this->manual->userAgent());
    }
}
