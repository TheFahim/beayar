<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'current_tenant_company_id',
        'current_scope',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function subscription(): HasOneThrough
    {
        // Redirect subscription access through Tenant
        // Note: This relies on the hasOneThrough relationship
        return $this->hasOneThrough(Subscription::class, Tenant::class);
    }

    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(TenantCompany::class, 'current_tenant_company_id');
    }

    public function ownedCompanies(): HasMany
    {
        return $this->hasMany(TenantCompany::class, 'owner_id');
    }

    public function companies()
    {
        return $this->belongsToMany(TenantCompany::class, 'company_members', 'user_id', 'tenant_company_id')
            ->withPivot('role', 'is_active', 'joined_at', 'employee_id')
            ->withTimestamps();
    }

    public function roleInCompany($companyId)
    {
        $company = $this->companies()->where('tenant_company_id', $companyId)->first();

        return $company ? $company->pivot->role : null;
    }

    public function isOwnerOf($companyId)
    {
        return $this->ownedCompanies()->where('id', $companyId)->exists();
    }

    // Subscription Helpers
    public function getCurrentCompanyId()
    {
        return $this->current_tenant_company_id ?? session('tenant_id');
    }

    /**
     * Get the team ID for Spatie Permissions.
     */
    public function getPermissionsTeamId()
    {
        return $this->getCurrentCompanyId();
    }

    public function getEffectiveSubscription()
    {
        // 1. If user is owner, they have subscription directly (via Tenant)
        if ($this->subscription) {
            return $this->subscription;
        }

        // 2. If user is employee, use current company's owner's subscription
        $companyId = $this->getCurrentCompanyId();

        if ($companyId) {
            $company = TenantCompany::find($companyId);
            if ($company && $company->owner && $company->owner->subscription) {
                return $company->owner->subscription;
            }
        }

        return null;
    }

    public function canPerformAction(string $metric): bool
    {
        $subscription = $this->getEffectiveSubscription();

        if (! $subscription) {
            return false;
        }

        return $subscription->isActive() && $subscription->checkLimit($metric);
    }

    public function hasModuleAccess(string $module): bool
    {
        $subscription = $this->getEffectiveSubscription();

        if (! $subscription) {
            return false;
        }

        return $subscription->hasModuleAccess($module);
    }

    public function recordActionUsage(string $metric, int $quantity = 1): void
    {
        $subscription = $this->getEffectiveSubscription();

        if ($subscription) {
            $subscription->recordUsage($metric, $quantity);
        }
    }

    public function hasFeatureAccess(string $feature): bool
    {
        $subscription = $this->getEffectiveSubscription();

        if (! $subscription) {
            return false;
        }

        return $subscription->hasFeatureAccess($feature);
    }
}
