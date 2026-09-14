<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->adminUser = User::where('email', 'admin@pospro.com')->first();
    }

    public function test_authenticated_admin_can_view_roles_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('roles.index'));

        $response->assertStatus(200);
        $response->assertSee('Hak Akses & Peran');
        $response->assertSee('super_admin');
    }

    public function test_can_create_new_role_with_permissions(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('roles.store'), [
            'name' => 'Supervisor Gudang',
            'permissions' => ['products.view', 'stocks.view', 'stocks.opname'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'supervisor_gudang']);

        $role = Role::findByName('supervisor_gudang');
        $this->assertTrue($role->hasPermissionTo('products.view'));
        $this->assertTrue($role->hasPermissionTo('stocks.view'));
        $this->assertFalse($role->hasPermissionTo('finance.accounts'));
    }

    public function test_can_update_role_permissions(): void
    {
        $role = Role::create(['name' => 'staf_kasir_junior', 'guard_name' => 'web']);
        $role->givePermissionTo('sales.pos');

        $response = $this->actingAs($this->adminUser)->put(route('roles.update', $role), [
            'name' => 'staf_kasir_senior',
            'permissions' => ['sales.pos', 'sales.view', 'sales.void'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $role->refresh();

        $this->assertEquals('staf_kasir_senior', $role->name);
        $this->assertTrue($role->hasPermissionTo('sales.void'));
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $superAdminRole = Role::findByName('super_admin');

        $response = $this->actingAs($this->adminUser)->delete(route('roles.destroy', $superAdminRole));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
    }

    public function test_cannot_delete_role_with_assigned_users(): void
    {
        $cashierRole = Role::findByName('cashier');

        $response = $this->actingAs($this->adminUser)->delete(route('roles.destroy', $cashierRole));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'cashier']);
    }

    public function test_can_delete_unused_custom_role(): void
    {
        $customRole = Role::create(['name' => 'magang_helper', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->delete(route('roles.destroy', $customRole));

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['name' => 'magang_helper']);
    }
}
