<?php

namespace Modules\PapersQuestions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paper extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
}
