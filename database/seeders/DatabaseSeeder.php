<?php

namespace Database\Seeders;

use App\Models\User;
use Modules\Schools\App\Models\School;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create at least one school
        $defaultSchool = School::firstOrCreate(
            ['name' => 'Default School'],
            [
                'address' => '123 Main Street, City',
                'contact' => '+1234567890',
            ]
        );

        // 2. Create admin and teacher users
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin User',
                'email' => 'sajidajaved604@gmail.com',
                'password' => bcrypt('password'),
                'phone_number' => '1234567890',
                'email_verified_at' => now(), // Mark admin email as verified
            ]
        );
        $teacher = User::firstOrCreate(
            ['username' => 'teacher'],
            [
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'password' => bcrypt('password'),
                'phone_number' => '09824554321',
            ]
        );

        // 3. Roles, permissions, and assignments
        $this->call(RolesAndPermissionsSeeder::class);

        // 4. Classes and sections (must come after schools)
        $this->call(\Modules\ClassesSections\Database\Seeders\ClassSeeder::class);
        $this->call(\Modules\ClassesSections\Database\Seeders\SectionSeeder::class);

        // 5. Admissions (students demo data)
        $this->call(\Modules\Admissions\Database\Seeders\AdmissionsDatabaseSeeder::class);
    }
}
