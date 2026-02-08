<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_user_company_id',
        'current_scope'
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
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'current_user_company_id');
    }

    public function ownedCompanies(): HasMany
    {
        return $this->hasMany(UserCompany::class, 'owner_id');
    }

    public function companies()
    {
        return $this->belongsToMany(UserCompany::class, 'company_members', 'user_id', 'user_company_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function roleInCompany($companyId)
    {
        $company = $this->companies()->where('user_company_id', $companyId)->first();
        return $company ? $company->pivot->role : null;
    }

    public function isOwnerOf($companyId)
    {
        return $this->ownedCompanies()->where('id', $companyId)->exists();
    }

    // Subscription Helpers
    public function canPerformAction(string $metric): bool
    {
        if (!$this->subscription) {
            return false;
        }
        return $this->subscription->isActive() && $this->subscription->checkLimit($metric);
    }

    public function recordActionUsage(string $metric, int $quantity = 1): void
    {
        if ($this->subscription) {
            $this->subscription->recordUsage($metric, $quantity);
        }
    }
}
