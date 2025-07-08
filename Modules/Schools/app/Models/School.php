<?php

namespace Modules\Schools\App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'schools';

    protected $fillable = [
        'name',
        'address',
        'contact',
    ];
}
