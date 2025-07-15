<?php

namespace Modules\Schools\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\ClassesSections\app\Models\ClassSchool;

class School extends Model
{
    use SoftDeletes;

    protected $table = 'schools';

    protected $fillable = [
        'name',
        'address',
        'contact',
        'logo',
        'main_image',
    ];

    protected $dates = ['deleted_at'];


    public function classes()
    {
        return $this->belongsToMany(ClassSchool::class);
    }
}
