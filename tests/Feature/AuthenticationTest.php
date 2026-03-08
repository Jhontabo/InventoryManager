<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_home_redirects_to_filament_login_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_redirects_to_admin(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user)->get('/admin/login');

        $response->assertRedirect('/admin');
    }
}
