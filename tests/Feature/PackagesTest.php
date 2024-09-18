<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_packages_json_is_protected_by_basic_auth(): void
    {
        $response = $this->get('/repo/plugin/plugin-slug/plugin-slug-1.0.0.zip');

        $response->assertStatus(401);
    }
}
