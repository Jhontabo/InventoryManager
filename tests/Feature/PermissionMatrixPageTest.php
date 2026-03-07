<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionMatrixPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_permission_matrix_page(): void
    {
        Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);

        $user = User::factory()->createOne();
        $user->assignRole('ADMIN');

        $this->actingAs($user)
            ->get('/admin/permission-matrix')
            ->assertSuccessful();
    }

    public function test_non_admin_cannot_access_permission_matrix_page(): void
    {
        $user = User::factory()->createOne();

        $this->actingAs($user)
            ->get('/admin/permission-matrix')
            ->assertForbidden();
    }
}
