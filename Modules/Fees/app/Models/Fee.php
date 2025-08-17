<?php

namespace Modules\Fees\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Fees\App\Models\FeeItem;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fee extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'student_id',
        'class_id',
        'type',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'voucher_number',
        'paid_voucher_image',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(\Modules\Admissions\App\Models\Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(\Modules\ClassesSections\App\Models\ClassModel::class, 'class_id');
    }

    public function feeItems(): HasMany
    {
        return $this->hasMany(FeeItem::class);
    }
}
