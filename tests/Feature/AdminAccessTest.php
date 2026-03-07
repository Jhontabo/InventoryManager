<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AdminAccessTest extends TestCase
{
    public function test_access_admin_without_auth(): void
    {
        $response = $this->get('/');

        $response->assertSuccessful()
            ->assertViewIs('auth.login');
    }

    public function test_access_admin_with_auth(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/admin');
    }

    public function test_dashboard_redirects_to_admin(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/admin');
    }
}
