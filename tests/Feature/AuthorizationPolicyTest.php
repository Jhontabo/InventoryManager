<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_user_without_permissions_cannot_create_or_edit_resources(): void
    {
        $user = User::factory()->createOne();
        $product = $this->createProduct();

        $this->assertFalse(Gate::forUser($user)->allows('create', Product::class));
        $this->assertFalse(Gate::forUser($user)->allows('update', $product));
        $this->assertFalse(Gate::forUser($user)->allows('delete', $product));
        $this->assertFalse(Gate::forUser($user)->allows('create', Laboratory::class));
    }

    public function test_user_with_specific_permissions_can_manage_products(): void
    {
        Permission::findOrCreate('create_product', 'web');
        Permission::findOrCreate('update_product', 'web');
        Permission::findOrCreate('delete_product', 'web');

        $user = User::factory()->createOne();
        $user->givePermissionTo(['create_product', 'update_product', 'delete_product']);

        $product = $this->createProduct();

        $this->assertTrue(Gate::forUser($user)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $product));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $product));
    }

    public function test_admin_role_with_seeded_permissions_can_create_and_update_resources(): void
    {
        $this->seed(\Database\Seeders\PermissionsSeeder::class);

        $user = User::factory()->createOne();
        $user->assignRole('ADMIN');

        $product = $this->createProduct();
        $laboratory = Laboratory::query()->create([
            'name' => 'Lab Integration',
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $product));
        $this->assertTrue(Gate::forUser($user)->allows('create', Laboratory::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $laboratory));
    }

    public function test_super_admin_role_bypasses_granular_permissions(): void
    {
        Role::firstOrCreate(['name' => 'SUPER-ADMIN', 'guard_name' => 'web']);

        $user = User::factory()->createOne();
        $user->assignRole('SUPER-ADMIN');

        $product = $this->createProduct();

        $this->assertTrue(Gate::forUser($user)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $product));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $product));
        $this->assertTrue(Gate::forUser($user)->allows('create', User::class));
    }

    private function createProduct(): Product
    {
        $laboratory = Laboratory::query()->create([
            'name' => 'Lab Test',
        ]);

        return Product::query()->create([
            'name' => 'Osciloscopio',
            'laboratory_id' => $laboratory->id,
        ]);
    }
}
