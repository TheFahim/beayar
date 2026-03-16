<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quotation extends BaseModel
{
    use BelongsToTenant, HasFactory;

    protected $guarded = ['id'];

    /**
     * Billing stage constants
     */
    const BILLING_STAGE_NONE = 'none';
    const BILLING_STAGE_ADVANCE_PENDING = 'advance_pending';
    const BILLING_STAGE_ADVANCE_ISSUED = 'advance_issued';
    const BILLING_STAGE_RUNNING_IN_PROGRESS = 'running_in_progress';
    const BILLING_STAGE_REGULAR_PENDING = 'regular_pending';
    const BILLING_STAGE_COMPLETED = 'completed';
    const BILLING_STAGE_CANCELLED = 'cancelled';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->reference_no) {
                $prefix = 'QT';

                // Use company settings prefix if available
                if ($model->tenant_company_id) {
                    $company = \App\Models\TenantCompany::find($model->tenant_company_id);
                    if ($company) {
                        $companyPrefix = $company->getSetting('quotation_prefix');
                        if (! empty($companyPrefix)) {
                            $prefix = rtrim($companyPrefix, '-');
                        }
                    }
                }

                $model->reference_no = $prefix.'-'.date('Y').'-'.strtoupper(Str::random(5));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(QuotationRevision::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function activeRevision()
    {
        return $this->hasOne(QuotationRevision::class)->where('is_active', true);
    }

    public function getActiveRevision()
    {
        return $this->activeRevision;
    }

    public function hasBills(): bool
    {
        return $this->bills()->exists();
    }

    public function hasChallan(): bool
    {
        $activeRevision = $this->getActiveRevision();

        return $activeRevision ? $activeRevision->hasChallan() : false;
    }

    public function isEditable(): bool
    {
        return ! $this->hasBills();
    }

    public function isDeletable(): bool
    {
        if ($this->hasBills()) {
            return false;
        }

        return ! $this->hasChallan();
    }
}
