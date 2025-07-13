<?php

namespace Modules\ClassesSections\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassesSections\app\Models\ClassSchool;
use Modules\Schools\app\Models\School;

class ClassSeeder extends Seeder
{
    public function run()
    {
        $schools = School::all();

        foreach ($schools as $school) {
            for ($i = 1; $i <= 12; $i++) {
                $class = ClassSchool::create(['name' => "Class $i"]);
                $school->classes()->attach($class->id);
            }
        }
    }
}
