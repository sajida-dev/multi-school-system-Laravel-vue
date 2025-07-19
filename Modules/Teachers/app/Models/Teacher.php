<?php

namespace Modules\Teachers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\Schools\App\Models\School;

class Teacher extends Model
{
    use SoftDeletes;

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'school_id',
        'cnic',
        'gender',
        'marital_status',
        'role',
        'dob',
        'salary',
        'contact_no',
        'date_of_joining',
        'experience_years',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
