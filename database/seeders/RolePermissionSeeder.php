<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            // User Management
            ['name' => 'view_users', 'description' => 'View user list and details', 'category' => 'user_management'],
            ['name' => 'create_users', 'description' => 'Create new users', 'category' => 'user_management'],
            ['name' => 'edit_users', 'description' => 'Edit user information', 'category' => 'user_management'],
            ['name' => 'delete_users', 'description' => 'Delete users', 'category' => 'user_management'],
            
            // Organization Management
            ['name' => 'view_organizations', 'description' => 'View organization list', 'category' => 'organization_management'],
            ['name' => 'approve_organizations', 'description' => 'Approve organization registrations', 'category' => 'organization_management'],
            ['name' => 'edit_organizations', 'description' => 'Edit organization details', 'category' => 'organization_management'],
            ['name' => 'delete_organizations', 'description' => 'Delete organizations', 'category' => 'organization_management'],
            
            // Opportunity Management
            ['name' => 'view_opportunities', 'description' => 'View all opportunities', 'category' => 'opportunity_management'],
            ['name' => 'create_opportunities', 'description' => 'Create new opportunities', 'category' => 'opportunity_management'],
            ['name' => 'edit_opportunities', 'description' => 'Edit opportunity details', 'category' => 'opportunity_management'],
            ['name' => 'delete_opportunities', 'description' => 'Delete opportunities', 'category' => 'opportunity_management'],
            
            // Application Management
            ['name' => 'view_applications', 'description' => 'View volunteer applications', 'category' => 'application_management'],
            ['name' => 'manage_applications', 'description' => 'Accept/reject applications', 'category' => 'application_management'],
            
            // System Administration
            ['name' => 'view_analytics', 'description' => 'View system analytics', 'category' => 'system_admin'],
            ['name' => 'manage_settings', 'description' => 'Manage system settings', 'category' => 'system_admin'],
            ['name' => 'manage_security', 'description' => 'Manage security settings', 'category' => 'system_admin'],
            ['name' => 'system_maintenance', 'description' => 'Perform system maintenance', 'category' => 'system_admin'],
            
            // Profile Management
            ['name' => 'manage_profile', 'description' => 'Manage own profile', 'category' => 'profile'],
            ['name' => 'apply_opportunities', 'description' => 'Apply for opportunities', 'category' => 'volunteer'],
            ['name' => 'view_own_applications', 'description' => 'View own applications', 'category' => 'volunteer'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Update existing roles with descriptions and colors
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Full system access and control',
                'color' => 'purple',
                'permissions' => [
                    'view_users', 'create_users', 'edit_users', 'delete_users',
                    'view_organizations', 'approve_organizations', 'edit_organizations', 'delete_organizations',
                    'view_opportunities', 'create_opportunities', 'edit_opportunities', 'delete_opportunities',
                    'view_applications', 'manage_applications',
                    'view_analytics', 'manage_settings', 'manage_security', 'system_maintenance',
                    'manage_profile'
                ]
            ],
            [
                'name' => 'organization',
                'description' => 'Manage organization opportunities and volunteers',
                'color' => 'blue',
                'permissions' => [
                    'view_opportunities', 'create_opportunities', 'edit_opportunities', 'delete_opportunities',
                    'view_applications', 'manage_applications',
                    'manage_profile'
                ]
            ],
            [
                'name' => 'volunteer',
                'description' => 'Apply for opportunities and manage profile',
                'color' => 'green',
                'permissions' => [
                    'view_opportunities', 'apply_opportunities', 'view_own_applications', 'manage_profile'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'description' => $roleData['description'],
                    'color' => $roleData['color']
                ]
            );

            // Update existing role if needed
            if (!$role->description) {
                $role->update([
                    'description' => $roleData['description'],
                    'color' => $roleData['color']
                ]);
            }

            // Attach permissions
            $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->permissions()->sync($permissions->pluck('id'));
        }
    }
}
