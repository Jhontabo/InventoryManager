<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminPanelSmokeTest extends TestCase
{
    public function test_guest_is_redirected_from_admin_panel_routes(): void
    {
        $this->get('/admin')->assertRedirect();
        $this->get('/admin/mi-perfil')->assertRedirect();
    }

    public function test_guest_is_redirected_from_protected_report_exports(): void
    {
        $this->get('/reports/dashboard')->assertRedirect();
        $this->get('/reports/excel')->assertRedirect();
    }

    public function test_guest_is_redirected_from_reports_page(): void
    {
        $this->get('/admin/reports')->assertRedirect();
    }

    public function test_legacy_reservation_history_url_redirects_to_new_slug_for_authenticated_user(): void
    {
        $this->actingAs(new User(['status' => 'active']));

        $this->get('/admin/reservation-historys')
            ->assertRedirect('/admin/reservation-histories');
    }
}
