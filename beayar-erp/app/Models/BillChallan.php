<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillChallan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bill_challans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bill_id',
        'challan_id',
    ];

    /**
     * Get the bill for this pivot.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the challan for this pivot.
     */
    public function challan(): BelongsTo
    {
        return $this->belongsTo(Challan::class);
    }

    /**
     * Get bill items associated with this pivot.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class, 'bill_challan_id');
    }
}
