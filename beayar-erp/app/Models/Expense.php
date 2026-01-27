<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id'];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];
}
