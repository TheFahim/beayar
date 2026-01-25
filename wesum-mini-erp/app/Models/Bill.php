<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Bill extends Model
{
    use HasFactory;
    public function challan()
    {
        return $this->belongsTo(Challan::class);
    }

    public function receivedBills()
    {
        return $this->hasMany(ReceivedBill::class);
    }
}
