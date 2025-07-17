<?php

namespace Modules\ClassesSections\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\ClassesSections\App\Models\Section;

class ClassSchool extends Model
{
    protected $table = 'classes'; // or 'classes' if that's your table name
    protected $fillable = ['name'];

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'class_section', 'class_id', 'section_id');
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
