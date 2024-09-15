<?php

namespace Tests\Feature\Updaters;

use App\Models\Package;
use App\Updaters\WpRocket;
use Tests\TestCase;

class WpRocketTest extends TestCase
{
    private WpRocket $wpRocket;

    public function setUp(): void
    {
        parent::setUp();

        $package = new Package([
            'slug' => 'wp-rocket',
            'updater' => 'wp_rocket',
            'settings' => [
            ],
        ]);

        $this->wpRocket = $package->updater();
    }

    public function test_validation_errors(): void
    {
        $this->assertEmpty($this->wpRocket->validationErrors()->all());

        putenv('WP_ROCKET_KEY');
        $this->assertContains('Env. variable WP_ROCKET_KEY is required', $this->wpRocket->validationErrors()->all());
        putenv('WP_ROCKET_EMAIL');
        $this->assertContains('Env. variable WP_ROCKET_EMAIL is required', $this->wpRocket->validationErrors()->all());
        putenv('WP_ROCKET_URL');
        $this->assertContains('Env. variable WP_ROCKET_URL is required', $this->wpRocket->validationErrors()->all());
    }

    public function test_fetch_title(): void
    {
        $this->assertEquals('Wp Rocket', $this->wpRocket->fetchTitle());
    }

    public function test_version_is_set(): void
    {
        $this->counterThrottling();
        $this->assertNotNull($this->wpRocket->version());
    }

    public function test_download_link_is_set(): void
    {
        $this->counterThrottling();
        $this->assertNotNull($this->wpRocket->downloadLink());
        $this->assertTrue(filter_var($this->wpRocket->downloadLink(), FILTER_VALIDATE_URL) !== false);
    }

    public function test_changelog_is_set(): void
    {
        $this->counterThrottling();
        $this->assertNotNull($this->wpRocket->changelog());
    }

    public function test_package_can_be_downloaded(): void
    {
        $this->assertTrue($this->wpRocket->testDownload());
    }

    public function test_user_agent_is_set(): void
    {
        $userAgent = sprintf('%1$s; %2$s;WP-Rocket|3.6.3|%3$s|%4$s|%2$s|8.2;',
            config('app.wp_user_agent'),
            getenv('WP_ROCKET_URL'),
            getenv('WP_ROCKET_KEY'),
            getenv('WP_ROCKET_EMAIL'),
        );
        $this->assertEquals($userAgent, $this->wpRocket->userAgent());

    }

    public function counterThrottling()
    {
        sleep(1);
    }
}
