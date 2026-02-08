<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCompany extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const TYPE_INDEPENDENT = 'independent';
    public const TYPE_HOLDING = 'holding';
    public const TYPE_SUBSIDIARY = 'subsidiary';

    public function isHolding(): bool
    {
        return $this->organization_type === self::TYPE_HOLDING;
    }

    public function isSubsidiary(): bool
    {
        return $this->organization_type === self::TYPE_SUBSIDIARY;
    }

    public function isIndependent(): bool
    {
        return $this->organization_type === self::TYPE_INDEPENDENT;
    }

    /**
     * Get IDs of the company and its subsidiaries if it's a holding company.
     * Useful for aggregated reporting.
     */
    public function getGroupIds(): array
    {
        if ($this->isHolding()) {
            return $this->subCompanies()->pluck('id')->push($this->id)->toArray();
        }
        return [$this->id];
    }

    /**
     * Scope to get the company and its subsidiaries.
     */
    public function scopeDescendantsOf($query, $companyId)
    {
        return $query->where('id', $companyId)
                     ->orWhere('parent_company_id', $companyId);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'company_members', 'user_company_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function parentCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'parent_company_id');
    }

    public function subCompanies(): HasMany
    {
        return $this->hasMany(UserCompany::class, 'parent_company_id');
    }

    public function customerCompanies(): HasMany
    {
        return $this->hasMany(CustomerCompany::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
