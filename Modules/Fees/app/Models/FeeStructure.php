<?php

namespace Modules\Fees\App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $fillable = [
        'class',
        'section',
        'type',
        'amount',
        'installments_count',
        'installment_amounts'
    ];

    protected $casts = [
        'installment_amounts' => 'array',
    ];
}
