<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'read user',
            'create user',
            'update user',
            'delete user',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $provider = Role::firstOrCreate(['name' => 'provider']);
        $member = Role::firstOrCreate(['name' => 'member']);

        // Assign permissions to roles
        $superadmin->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // Create Users and assign roles
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->assignRole($superadmin);

        $providerUser = User::firstOrCreate(
            ['email' => 'provider@example.com'],
            [
                'name' => 'Provider User',
                'password' => Hash::make('password'),
            ]
        );
        $providerUser->assignRole($provider);

        $memberUser = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Member User',
                'password' => Hash::make('password'),
            ]
        );
        $memberUser->assignRole($member);
    }
}
