<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_users_have_roles()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($admin->hasRole('superadmin'));

        $provider = User::where('email', 'provider@example.com')->first();
        $this->assertTrue($provider->hasRole('provider'));

        $member = User::where('email', 'member@example.com')->first();
        $this->assertTrue($member->hasRole('member'));
    }

    public function test_new_user_assigns_default_role()
    {
        $userData = [
            'username' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($user->hasRole('member'));
    }
}
