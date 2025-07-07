<?php

namespace Modules\Admissions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdmissionsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('students')->insert([
            [
                'nationality' => 'Pakistan',
                'registration_number' => 'REG-1001',
                'name' => 'Ali Raza',
                'b_form_number' => '38201-1234567-1',
                'admission_date' => Carbon::now()->subDays(10),
                'date_of_birth' => '2015-05-10',
                'class' => 'ONE',
                'gender' => 'Male',
                'class_shift' => 'Morning',
                'previous_school' => 'NIL',
                'inclusive' => 'No Disability',
                'other_inclusive_type' => null,
                'religion' => 'Islam',
                'is_bricklin' => false,
                'is_orphan' => false,
                'is_qsc' => false,
                'profile_photo_path' => null,
                'father_name' => 'Muhammad Bilal',
                'guardian_name' => 'Fawad Raza',
                'father_cnic' => '38201-0192803-7',
                'mother_cnic' => '38201-3987353-4',
                'father_profession' => 'Private/Self Employed',
                'no_of_children' => 3,
                'job_type' => 'Private/Self Employed',
                'father_education' => 'Primary',
                'mother_education' => 'Middle',
                'mother_profession' => 'House Wife',
                'father_income' => 'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
                'mother_income' => 'NOT APPLICABLE',
                'household_income' => 'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
                'permanent_address' => 'Girote',
                'phone_no' => '042-5678123',
                'mobile_no' => '307-678-1366',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nationality' => 'India',
                'registration_number' => 'REG-1002',
                'name' => 'Sara Khan',
                'b_form_number' => '38201-7654321-2',
                'admission_date' => Carbon::now()->subDays(5),
                'date_of_birth' => '2014-08-15',
                'class' => 'TWO',
                'gender' => 'Female',
                'class_shift' => 'Morning',
                'previous_school' => 'NIL',
                'inclusive' => 'No Disability',
                'other_inclusive_type' => null,
                'religion' => 'Islam',
                'is_bricklin' => false,
                'is_orphan' => false,
                'is_qsc' => false,
                'profile_photo_path' => null,
                'father_name' => 'Imran Khan',
                'guardian_name' => 'Ayesha Khan',
                'father_cnic' => '38201-0192803-8',
                'mother_cnic' => '38201-3987353-5',
                'father_profession' => 'Government',
                'no_of_children' => 2,
                'job_type' => 'Government',
                'father_education' => 'Graduate',
                'mother_education' => 'Matric',
                'mother_profession' => 'Private/Self Employed',
                'father_income' => 'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
                'mother_income' => 'INCOME LEVEL BETWEEN RS. 20,001 - 27,000',
                'household_income' => 'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
                'permanent_address' => 'Lahore',
                'phone_no' => '042-1234567',
                'mobile_no' => '300-123-4567',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
