<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SaleTarget extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'achieved_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
