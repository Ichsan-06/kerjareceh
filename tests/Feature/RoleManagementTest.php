<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_can_list_roles()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'permissions']
            ]);
    }

    public function test_can_create_role_with_permissions()
    {
        $permissions = Permission::limit(2)->get()->pluck('name')->toArray();
        $roleData = [
            'name' => 'manager',
            'permissions' => $permissions
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/roles', $roleData);

        $response->assertStatus(201)
            ->assertJson(['name' => 'manager']);

        $role = Role::where('name', 'manager')->first();
        $this->assertTrue($role->hasPermissionTo($permissions[0]));
    }

    public function test_can_update_role_permissions()
    {
        $role = Role::create(['name' => 'editor']);
        $permissions = Permission::limit(2)->get()->pluck('name')->toArray();

        $updateData = [
            'name' => 'senior editor',
            'permissions' => $permissions
        ];

        $response = $this->actingAs($this->admin)->putJson("/api/roles/{$role->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['name' => 'senior editor']);

        $role->refresh();
        $this->assertEquals('senior editor', $role->name);
        $this->assertTrue($role->hasPermissionTo($permissions[0]));
    }

    public function test_can_delete_role()
    {
        $role = Role::create(['name' => 'temp_role']);

        $response = $this->actingAs($this->admin)->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_can_list_grouped_permissions()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/roles/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [] // Expecting 'user' group from seeder
            ]);
    }
}
