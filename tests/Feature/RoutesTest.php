<?php

namespace Tests\Feature;

use Tests\TestCase;

class RoutesTest extends TestCase
{
    public function test_the_application_returns_a_redirect(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }

    public function test_the_application_login_returns_a_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
