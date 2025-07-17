<?php

namespace Modules\ClassesSections\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\ClassesSections\App\Models\ClassSchool;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'class_id',
    ];

    protected $dates = ['deleted_at'];

    public function classes()
    {
        return $this->belongsToMany(ClassSchool::class, 'class_section', 'section_id', 'class_id');
    }

    // Optional: If you want to access the parent class directly
    // public function class()
    // {
    //     return $this->belongsTo(ClassSchool::class, 'class_id');
    // }
}
