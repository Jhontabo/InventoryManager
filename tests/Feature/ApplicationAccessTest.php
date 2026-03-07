<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ApplicationAccessTest extends TestCase
{
    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_loads_fails(): void
    {
        // Usuario no autenticado - debe recibir 404
        $response = $this->get('/dashboard');
        $response->assertStatus(404);
    }

    public function test_authenticated_user_is_redirected_from_home_to_dashboard(): void
    {
        $this->actingAs(new User(['status' => 'active']));

        $this->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_user_is_redirected_from_dashboard_to_admin(): void
    {
        $this->actingAs(new User(['status' => 'active']));

        $this->get('/dashboard')
            ->assertRedirect('/admin');
    }
}
