<?php

namespace Modules\Fees\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Fees\App\Models\FeeItem;

class Fee extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'voucher_number'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(\Modules\Admissions\App\Models\Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeItem::class);
    }
}
