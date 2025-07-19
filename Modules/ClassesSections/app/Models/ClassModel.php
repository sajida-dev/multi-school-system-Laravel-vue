<?php

namespace Modules\ClassesSections\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\ClassesSections\App\Models\Section;

class ClassModel extends Model
{
    protected $table = 'classes'; // or 'classes' if that's your table name
    protected $fillable = ['name'];

    public function sections()
    {
        // Updated to use the plural class_school_sections pivot table
        return $this->belongsToMany(
            Section::class,
            'class_school_sections',
            'class_school_id',
            'section_id'
        );
    }

    public function schools()
    {
        return $this->belongsToMany(
            \Modules\Schools\App\Models\School::class,
            'class_schools',
            'class_id',
            'school_id'
        );
    }
}
