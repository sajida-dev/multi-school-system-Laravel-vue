<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Modules\Schools\App\Models\School;

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
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web', 'school_id' => null]);
        $superAdminRole->syncPermissions(Permission::all());

        // 3. Create demo schools
        $schoolA = School::firstOrCreate(['name' => 'Alpha School'], ['address' => 'Alpha St', 'contact' => '111-111']);
        $schoolB = School::firstOrCreate(['name' => 'Beta School'], ['address' => 'Beta St', 'contact' => '222-222']);
        $schools = [$schoolA, $schoolB];

        // 4. Create Super Admin user (with unique phone_number)
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
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        if (!$superAdmin->hasRole('superadmin')) {
            $superAdmin->assignRole('superadmin');
        }

        // 5. Create and assign Admin/Principal roles per school
        foreach ($schools as $school) {
            // Create roles for this school
            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'web',
                'school_id' => $school->id
            ]);
            $principalRole = Role::firstOrCreate([
                'name' => 'principal',
                'guard_name' => 'web',
                'school_id' => $school->id
            ]);

            // Create an admin user for each school (with unique phone_number)
            $adminUser = User::firstOrCreate(
                ['username' => 'admin_' . strtolower(str_replace(' ', '_', $school->name))],
                [
                    'name' => $school->name . ' Admin',
                    'email' => 'admin.' . strtolower(str_replace(' ', '.', $school->name)) . '@example.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'phone_number' => '+100000000' . $school->id, // unique per school
                ]
            );
            app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
            if (!$adminUser->hasRole('admin')) {
                $adminUser->assignRole('admin');
            }
        }

        // Reset team context after seeding
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        // Clear permission cache after seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
