<?php

namespace Modules\ClassesSections\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassesSections\app\Models\ClassSchool;
use Modules\ClassesSections\app\Models\SchoolClass;

class SectionSeeder extends Seeder
{
    public function run()
    {
        $sections = range('A', 'Z');
        $classes = ClassSchool::all();

        foreach ($classes as $class) {
            foreach ($sections as $section) {
                $class->sections()->create([
                    'name' => $section,
                ]);
            }
        }
    }
}
