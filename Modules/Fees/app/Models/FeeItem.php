<?php

namespace Modules\Fees\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeItem extends Model
{
    protected $fillable = [
        'fee_id',
        'description',
        'amount'
    ];

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }
}
