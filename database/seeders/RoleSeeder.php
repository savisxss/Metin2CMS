<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users', 
            'edit users',
            'delete users',
            
            // Account management
            'view accounts',
            'edit accounts',
            'ban accounts',
            'unban accounts',
            
            // Content management
            'view news',
            'create news',
            'edit news',
            'delete news',
            'publish news',
            
            // Settings
            'view settings',
            'edit settings',
            
            // Donations
            'view donations',
            'process donations',
            
            // Vouchers
            'view vouchers',
            'create vouchers',
            'edit vouchers',
            'delete vouchers',
            
            // Logs
            'view logs',
            
            // Admin panel access
            'access admin',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $moderatorRole = Role::create(['name' => 'moderator']);
        $playerRole = Role::create(['name' => 'player']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $moderatorRole->givePermissionTo([
            'view users',
            'view accounts',
            'ban accounts',
            'unban accounts',
            'view news',
            'create news',
            'edit news',
            'publish news',
            'view donations',
            'view vouchers',
            'view logs',
            'access admin',
        ]);

        // Players get no special permissions by default
    }
}