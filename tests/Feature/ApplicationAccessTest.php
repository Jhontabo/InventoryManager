<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ApplicationAccessTest extends TestCase
{
    public function test_home_redirects_to_filament_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin/login');
    }

    public function test_dashboard_route_redirects_to_admin(): void
    {
        // La ruta dashboard redirige al admin.
        $response = $this->get('/dashboard');
        $response->assertRedirect('/admin');
    }

    public function test_authenticated_user_is_redirected_from_home_to_dashboard(): void
    {
        $this->actingAs(new User(['status' => 'active']));

        $this->get('/')
            ->assertRedirect('/admin');
    }

    public function test_authenticated_user_is_redirected_from_dashboard_to_admin(): void
    {
        $this->actingAs(new User(['status' => 'active']));

        $this->get('/dashboard')
            ->assertRedirect('/admin');
    }
}
