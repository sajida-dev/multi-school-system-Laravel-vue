<?php

namespace Modules\ClassesSections\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Schools\app\Models\School;
use Modules\ClassesSections\app\Models\Section;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSchool extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
    ];


    protected $dates = ['deleted_at'];

    public function schools()
    {
        return $this->belongsToMany(School::class);
    }
    public function sections()
    {
        return $this->belongsToMany(Section::class);
    }
}
