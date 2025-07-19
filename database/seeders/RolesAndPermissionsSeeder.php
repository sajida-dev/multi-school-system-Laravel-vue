<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Modules\Schools\App\Models\School;
use App\Models\UserPassword;
use Illuminate\Support\Facades\Crypt;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache before seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions (CRUD for each module + full control)
        $modules = ['users', 'schools', 'classes', 'sections', 'admissions', 'fees', 'papers', 'results', 'certificates', 'reports'];
        $actions = ['create', 'read', 'update', 'delete'];
        $permissions = ['full control'];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[] = "$action $module";
            }
        }
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Create global superadmin role (school_id => null)
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web', 'school_id' => null]
        );
        $superAdminRole->syncPermissions(Permission::all());

        // 3. Create Super Admin user (with unique phone_number)
        $superAdmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'phone_number' => '+19999999999', // unique phone number for superadmin
            ]
        );
        // Store the real password in user_passwords table
        UserPassword::updateOrCreate(
            ['user_id' => $superAdmin->id],
            ['password_encrypted' => Crypt::encryptString('password')]
        );
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        if (!$superAdmin->hasRole('superadmin')) {
            $superAdmin->assignRole('superadmin');
        }

        // Clear permission cache after seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
