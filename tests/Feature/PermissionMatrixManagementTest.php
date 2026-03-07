<?php

namespace Tests\Feature;

use App\Filament\Pages\PermissionMatrix;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionMatrixManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_clone_permissions_between_roles(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
        $sourceRole = Role::firstOrCreate(['name' => 'DOCENTE', 'guard_name' => 'web']);
        $targetRole = Role::firstOrCreate(['name' => 'LABORATORISTA', 'guard_name' => 'web']);

        $createProduct = Permission::findOrCreate('create_product', 'web');
        $updateProduct = Permission::findOrCreate('update_product', 'web');

        $sourceRole->syncPermissions([$createProduct]);
        $targetRole->syncPermissions([$updateProduct]);

        $admin = User::factory()->createOne();
        $admin->assignRole($adminRole);

        Livewire::actingAs($admin)
            ->test(PermissionMatrix::class)
            ->call('copyPermissionsBetweenRoles', 'DOCENTE', 'LABORATORISTA');

        $targetRole->refresh();

        $this->assertTrue($targetRole->hasPermissionTo('create_product'));
        $this->assertFalse($targetRole->hasPermissionTo('update_product'));
    }
}
