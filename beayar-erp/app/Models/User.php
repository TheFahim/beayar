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
            ->withPivot('role', 'is_active')
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
    public function canPerformAction(string $metric): bool
    {
        if (! $this->subscription) {
            return false;
        }

        return $this->subscription->isActive() && $this->subscription->checkLimit($metric);
    }

    public function hasModuleAccess(string $module): bool
    {
        if (! $this->subscription) {
            return false;
        }

        return $this->subscription->hasModuleAccess($module);
    }

    public function recordActionUsage(string $metric, int $quantity = 1): void
    {
        if ($this->subscription) {
            $this->subscription->recordUsage($metric, $quantity);
        }
    }
}
