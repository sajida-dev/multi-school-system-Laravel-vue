<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Optionally clear users table for dev only (uncomment if needed)
        // \DB::table('users')->truncate();

        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::firstOrCreate(
            [
                'username' => 'admin',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'phone_number' => '1234567890',
            ]
        );
        $data = User::firstOrCreate(
            [
                'username' => 'teacher',
            ],
            [
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'password' => bcrypt('password'),
                'phone_number' => '09824554321',
            ]
        );

        // Assign roles if using spatie/laravel-permission
        if (method_exists($admin, 'assignRole')) {
            $admin->assignRole('admin');
        }
        if (method_exists($data, 'assignRole')) {
            $data->assignRole('teacher');
        }

        $this->call(\Modules\Admissions\Database\Seeders\AdmissionsDatabaseSeeder::class);
    }
}
