<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_company_id',
        'quotation_id',
        'quotation_revision_id',
        'parent_bill_id',
        'invoice_no',
        'bill_date',
        'payment_received_date',
        'bill_type',
        'total_amount',
        'bill_amount',
        'bill_percentage',
        'due',
        'shipping',
        'discount',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bill_date' => 'date',
        'payment_received_date' => 'date',
        'total_amount' => 'decimal:2',
        'bill_percentage' => 'decimal:2',
        'paid' => 'decimal:2',
        'paid_percent' => 'decimal:2',
        'due' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function quotationRevision()
    {
        return $this->belongsTo(QuotationRevision::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Bill::class, 'parent_bill_id');
    }

    public function children()
    {
        return $this->hasMany(Bill::class, 'parent_bill_id');
    }

    public function challans()
    {
        return $this->belongsToMany(Challan::class, 'bill_challans')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function items()
    {
        return $this->hasManyThrough(
            BillItem::class,
            BillChallan::class,
            'bill_id',          // Foreign key on bill_challans referencing bills
            'bill_challan_id',  // Foreign key on bill_items referencing bill_challans
            'id',               // Local key on bills
            'id'                // Local key on bill_challans
        );
    }

    // Removed bill_installments relations

    public function receivedBills()
    {
        return $this->hasMany(ReceivedBill::class);
    }

    public function isAdvance()
    {
        return $this->bill_type === 'advance';
    }

    public function isRunning()
    {
        return $this->bill_type === 'running';
    }

    public function isRegular()
    {
        return $this->bill_type === 'regular';
    }
}
