# Multi-User SaaS Upgrade Guide
## Step-by-Step Implementation Instructions

**Version:** 2.0 (Production Secure)  
**Estimated Time:** 4-6 hours  
**Difficulty:** Intermediate  
**Downtime Required:** 30-60 minutes (for database migration)

---

## Table of Contents

- [Phase 0: Pre-Upgrade Preparation](#phase-0-pre-upgrade-preparation)
- [Phase 1: Create New Infrastructure Code](#phase-1-create-new-infrastructure-code)
- [Phase 2: Update Models](#phase-2-update-models)
- [Phase 3: Create Authorization Layer](#phase-3-create-authorization-layer)
- [Phase 4: Database Migration](#phase-4-database-migration)
- [Phase 5: Update Controllers](#phase-5-update-controllers)
- [Phase 6: Testing & Validation](#phase-6-testing--validation)
- [Phase 7: Production Deployment](#phase-7-production-deployment)
- [Phase 8: Post-Deployment Monitoring](#phase-8-post-deployment-monitoring)
- [Rollback Procedures](#rollback-procedures)

---

## Phase 0: Pre-Upgrade Preparation

### Step 0.1: Backup Everything

**⚠️ CRITICAL: Do not proceed without backups!**

```bash
# 1. Backup your database
mysqldump -u your_username -p beayar > backup_beayar_$(date +%Y%m%d_%H%M%S).sql

# 2. Backup your entire Laravel project
cd /path/to/your/project
tar -czf ../beayar_backup_$(date +%Y%m%d_%H%M%S).tar.gz .

# 3. Verify backups exist
ls -lh ../beayar_backup_*
ls -lh backup_beayar_*
```

**✅ Checkpoint:** You should have two backup files before proceeding.

---

### Step 0.2: Setup Testing Environment

```bash
# 1. Create a staging/testing database
mysql -u your_username -p -e "CREATE DATABASE beayar_staging;"

# 2. Import current data to staging
mysql -u your_username -p beayar_staging < backup_beayar_$(date +%Y%m%d_%H%M%S).sql

# 3. Update your .env.testing or create .env.staging
cp .env .env.staging
```

Edit `.env.staging`:
```env
DB_DATABASE=beayar_staging
```

**✅ Checkpoint:** Staging database created and populated.

---

### Step 0.3: Check Current System State

```bash
# 1. Check Laravel version
php artisan --version

# 2. Check current migrations
php artisan migrate:status

# 3. Check if Spatie Permission is installed
composer show spatie/laravel-permission

# 4. List current routes
php artisan route:list | grep -E "quotation|bill|challan"
```

**✅ Checkpoint:** Document your current state for reference.

---

## Phase 1: Create New Infrastructure Code

### Step 1.1: Create Tenant Scope Infrastructure

**File:** `app/Scopes/TenantScope.php`

```bash
mkdir -p app/Scopes
touch app/Scopes/TenantScope.php
```

Add this content:

```php
<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Only apply if user is authenticated and has a current company
        if (auth()->check() && auth()->user()->currentCompanyId()) {
            $builder->where($model->getTable() . '.user_company_id', '=', auth()->user()->currentCompanyId());
        }
    }
    
    /**
     * Extend the query builder with methods to bypass the scope
     */
    public function extend(Builder $builder)
    {
        // Add method to query across all tenants (admin use)
        $builder->macro('withAllTenants', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
        
        // Add method to query a specific tenant
        $builder->macro('forTenant', function (Builder $builder, $tenantId) {
            return $builder->withoutGlobalScope($this)
                          ->where($builder->getModel()->getTable() . '.user_company_id', $tenantId);
        });
    }
}
```

**✅ Checkpoint:** File created at `app/Scopes/TenantScope.php`

---

### Step 1.2: Create BelongsToTenant Trait

**File:** `app/Traits/BelongsToTenant.php`

```bash
mkdir -p app/Traits
touch app/Traits/BelongsToTenant.php
```

Add this content:

```php
<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait
     */
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope);
        
        // Automatically set user_company_id when creating
        static::creating(function (Model $model) {
            if (!$model->user_company_id && auth()->check()) {
                $model->user_company_id = auth()->user()->currentCompanyId();
            }
        });
    }
}
```

**✅ Checkpoint:** File created at `app/Traits/BelongsToTenant.php`

---

### Step 1.3: Create Tenant Context Middleware

**File:** `app/Http/Middleware/SetTenantContext.php`

```bash
php artisan make:middleware SetTenantContext
```

Replace the generated content with:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetTenantContext
{
    /**
     * Handle an incoming request and ensure tenant context is set
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        
        // If user doesn't have a current company set
        if (!$user->current_user_company_id) {
            // Get their first active company
            $firstCompany = $user->activeCompanies()->first();
            
            if (!$firstCompany) {
                // User not a member of any company
                return redirect()->route('no-company-access')
                    ->with('error', 'You do not have access to any company.');
            }
            
            // Set current company
            $user->update(['current_user_company_id' => $firstCompany->id]);
        }
        
        // Verify user still has access to current company
        if (!$user->belongsToCompany($user->current_user_company_id)) {
            // Access was revoked, clear current company
            $user->update(['current_user_company_id' => null]);
            
            return redirect()->route('select-company')
                ->with('error', 'Your access to this company has been revoked.');
        }
        
        // Set tenant context in session for easy access
        session(['current_company_id' => $user->current_user_company_id]);
        
        return $next($request);
    }
}
```

**✅ Checkpoint:** Middleware created at `app/Http/Middleware/SetTenantContext.php`

---

### Step 1.4: Create Company Member Service

**File:** `app/Services/CompanyMemberService.php`

```bash
mkdir -p app/Services
touch app/Services/CompanyMemberService.php
```

Add this content:

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyMemberService
{
    /**
     * Invite a new user to a company
     */
    public function inviteUser(UserCompany $company, array $data, User $inviter)
    {
        return DB::transaction(function () use ($company, $data, $inviter) {
            // Check if user exists
            $user = User::where('email', $data['email'])->first();
            
            $isNewUser = false;
            
            if (!$user) {
                // Create new user account
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make(Str::random(32)), // Temporary password
                    'is_active' => true,
                    'current_user_company_id' => $company->id,
                ]);
                
                $isNewUser = true;
            }
            
            // Check if already a member
            $existingMembership = $company->members()
                ->withTrashed()
                ->where('user_id', $user->id)
                ->first();
            
            if ($existingMembership) {
                // Reactivate if was deactivated
                $company->members()->updateExistingPivot($user->id, [
                    'role' => $data['role'],
                    'is_active' => true,
                    'deactivated_at' => null,
                    'deleted_at' => null,
                    'invited_by' => $inviter->id,
                    'invited_at' => now(),
                ]);
            } else {
                // Add as new member
                $company->members()->attach($user->id, [
                    'role' => $data['role'],
                    'is_active' => true,
                    'invited_by' => $inviter->id,
                    'invited_at' => now(),
                    'joined_at' => $isNewUser ? now() : null,
                ]);
            }
            
            return $user;
        });
    }
    
    /**
     * Deactivate a user's access to a company (RECOMMENDED way to remove users)
     */
    public function deactivateMember(UserCompany $company, User $user)
    {
        return DB::transaction(function () use ($company, $user) {
            $company->members()->updateExistingPivot($user->id, [
                'is_active' => false,
                'deactivated_at' => now(),
            ]);
            
            // If this was their current company, clear it
            if ($user->current_user_company_id === $company->id) {
                $user->update(['current_user_company_id' => null]);
            }
            
            // Log the action
            activity()
                ->performedOn($company)
                ->causedBy(auth()->user())
                ->withProperties(['deactivated_user_id' => $user->id])
                ->log('User deactivated from company');
            
            return true;
        });
    }
    
    /**
     * Permanently remove user from company (soft delete pivot)
     */
    public function removeMember(UserCompany $company, User $user)
    {
        return DB::transaction(function () use ($company, $user) {
            // First deactivate
            $this->deactivateMember($company, $user);
            
            // Then soft delete the pivot relationship
            DB::table('company_users')
                ->where('user_company_id', $company->id)
                ->where('user_id', $user->id)
                ->update(['deleted_at' => now()]);
            
            return true;
        });
    }
    
    /**
     * Reactivate a previously deactivated user
     */
    public function reactivateMember(UserCompany $company, User $user)
    {
        return DB::transaction(function () use ($company, $user) {
            $company->members()
                ->withTrashed()
                ->updateExistingPivot($user->id, [
                    'is_active' => true,
                    'deactivated_at' => null,
                    'deleted_at' => null,
                ]);
            
            activity()
                ->performedOn($company)
                ->causedBy(auth()->user())
                ->withProperties(['reactivated_user_id' => $user->id])
                ->log('User reactivated in company');
            
            return true;
        });
    }
    
    /**
     * Change a user's role in a company
     */
    public function changeRole(UserCompany $company, User $user, string $newRole)
    {
        if (!in_array($newRole, ['admin', 'employee'])) {
            throw new \InvalidArgumentException('Invalid role. Must be admin or employee.');
        }
        
        $company->members()->updateExistingPivot($user->id, [
            'role' => $newRole,
        ]);
        
        activity()
            ->performedOn($company)
            ->causedBy(auth()->user())
            ->withProperties([
                'user_id' => $user->id,
                'new_role' => $newRole,
            ])
            ->log('User role changed');
        
        return true;
    }
}
```

**✅ Checkpoint:** Service created at `app/Services/CompanyMemberService.php`

---

## Phase 2: Update Models

### Step 2.1: Update User Model

**File:** `app/Models/User.php`

Open your existing `app/Models/User.php` and add these methods (keep existing code):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_user_company_id',
        'current_scope',
        'is_active',
        'deactivated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    // ========================================
    // COMPANY RELATIONSHIPS
    // ========================================
    
    /**
     * Companies this user owns (as the subscription payer)
     */
    public function ownedCompanies()
    {
        return $this->hasMany(UserCompany::class, 'owner_id');
    }
    
    /**
     * Companies this user belongs to (including inactive)
     */
    public function companies()
    {
        return $this->belongsToMany(UserCompany::class, 'company_users')
                    ->withPivot(['role', 'is_active', 'invited_by', 'joined_at', 'deactivated_at', 'deleted_at'])
                    ->withTimestamps();
    }
    
    /**
     * Only active company memberships
     */
    public function activeCompanies()
    {
        return $this->belongsToMany(UserCompany::class, 'company_users')
                    ->withPivot(['role', 'is_active', 'invited_by', 'joined_at'])
                    ->wherePivot('is_active', true)
                    ->wherePivot('deleted_at', null)
                    ->withTimestamps();
    }
    
    /**
     * Current active company
     */
    public function currentCompany()
    {
        return $this->belongsTo(UserCompany::class, 'current_user_company_id');
    }

    // ========================================
    // ROLE & PERMISSION HELPERS
    // ========================================
    
    /**
     * Get current active company ID from context
     */
    public function currentCompanyId()
    {
        return $this->current_user_company_id;
    }
    
    /**
     * Get user's role in a specific company
     * 
     * @param int $companyId
     * @return string|null 'owner', 'admin', 'employee', or null
     */
    public function roleInCompany($companyId)
    {
        $membership = $this->companies()
            ->where('user_companies.id', $companyId)
            ->wherePivot('is_active', true)
            ->wherePivot('deleted_at', null)
            ->first();
        
        return $membership ? $membership->pivot->role : null;
    }
    
    /**
     * Check if user has a specific role in a company
     * 
     * @param int $companyId
     * @param string|array $roles
     * @return bool
     */
    public function hasRoleInCompany($companyId, $roles)
    {
        $userRole = $this->roleInCompany($companyId);
        
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        
        return $userRole === $roles;
    }
    
    /**
     * Check if user is owner of a company
     */
    public function isOwnerOf($companyId)
    {
        return $this->hasRoleInCompany($companyId, 'owner');
    }
    
    /**
     * Check if user is admin or owner of a company
     */
    public function canManageCompany($companyId)
    {
        return $this->hasRoleInCompany($companyId, ['owner', 'admin']);
    }
    
    /**
     * Check if user is an active member of a company
     */
    public function belongsToCompany($companyId)
    {
        return $this->companies()
            ->where('user_companies.id', $companyId)
            ->wherePivot('is_active', true)
            ->wherePivot('deleted_at', null)
            ->exists();
    }
}
```

**✅ Checkpoint:** User model updated with new methods.

---

### Step 2.2: Update UserCompany Model

**File:** `app/Models/UserCompany.php`

Replace or update your existing `app/Models/UserCompany.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCompany extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'owner_id',
        'parent_company_id',
        'name',
        'email',
        'phone',
        'address',
        'bin_no',
        'logo',
        'status',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    /**
     * The owner who pays for the subscription
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    
    /**
     * All members (includes owner + employees)
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'company_users')
                    ->withPivot(['role', 'is_active', 'invited_by', 'joined_at', 'deactivated_at', 'deleted_at'])
                    ->withTimestamps();
    }
    
    /**
     * Only active members
     */
    public function activeMembers()
    {
        return $this->members()
                    ->wherePivot('is_active', true)
                    ->wherePivot('deleted_at', null);
    }
    
    /**
     * Only employees (exclude owner if needed)
     */
    public function employees()
    {
        return $this->activeMembers()
                    ->wherePivot('role', 'employee');
    }
    
    /**
     * Admins in the company
     */
    public function admins()
    {
        return $this->activeMembers()
                    ->wherePivot('role', 'admin');
    }
    
    /**
     * Business relationships
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }
    
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
    
    public function challans()
    {
        return $this->hasMany(Challan::class);
    }
    
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * Get active subscription
     */
    public function activeSubscription()
    {
        return $this->hasOneThrough(
            \App\Models\Subscription::class,
            User::class,
            'id',
            'user_id',
            'owner_id',
            'id'
        )->where('status', 'active')
          ->where('ends_at', '>', now());
    }
}
```

**✅ Checkpoint:** UserCompany model updated.

---

### Step 2.3: Update Quotation Model

**File:** `app/Models/Quotation.php`

Update your existing Quotation model:

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    
    protected $fillable = [
        'user_company_id',
        'created_by_user_id',
        'updated_by_user_id',
        'client_id',
        'quotation_no',
        'quotation_date',
        'valid_until',
        'total_amount',
        'discount',
        'tax',
        'status',
        'notes',
        // ... add other fields from your schema
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function userCompany()
    {
        return $this->belongsTo(UserCompany::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ========================================
    // BOOT METHOD - Auto-set creator/updater
    // ========================================
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by_user_id = auth()->id();
                $model->updated_by_user_id = auth()->id();
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by_user_id = auth()->id();
            }
        });
    }
}
```

**✅ Checkpoint:** Quotation model updated with BelongsToTenant trait.

---

### Step 2.4: Update Bill Model

**File:** `app/Models/Bill.php`

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    
    protected $fillable = [
        'user_company_id',
        'created_by_user_id',
        'updated_by_user_id',
        'quotation_id',
        'quotation_revision_id',
        'parent_bill_id',
        'bill_type',
        'invoice_no',
        'bill_date',
        'payment_received_date',
        'total_amount',
        'bill_percentage',
        'bill_amount',
        'due',
        'shipping',
        'discount',
        'status',
        'notes',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'payment_received_date' => 'date',
        'total_amount' => 'decimal:2',
        'bill_percentage' => 'decimal:2',
        'bill_amount' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function userCompany()
    {
        return $this->belongsTo(UserCompany::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
    
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by_user_id = auth()->id();
                $model->updated_by_user_id = auth()->id();
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by_user_id = auth()->id();
            }
        });
    }
}
```

**✅ Checkpoint:** Bill model updated.

---

### Step 2.5: Update Challan Model

**File:** `app/Models/Challan.php`

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Challan extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    
    protected $fillable = [
        'user_company_id',
        'created_by_user_id',
        'updated_by_user_id',
        'quotation_id',
        'quotation_revision_id',
        'challan_no',
        'challan_date',
        'delivery_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'challan_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function userCompany()
    {
        return $this->belongsTo(UserCompany::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by_user_id = auth()->id();
                $model->updated_by_user_id = auth()->id();
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by_user_id = auth()->id();
            }
        });
    }
}
```

**✅ Checkpoint:** Challan model updated.

---

### Step 2.6: Update Client, Product, and Other Models

Apply the same pattern to:
- `app/Models/Client.php`
- `app/Models/Product.php`
- `app/Models/BrandOrigin.php`
- `app/Models/ProductCategory.php`

**Template for each:**

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class [ModelName] extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $fillable = [
        'user_company_id',
        'created_by_user_id',
        'updated_by_user_id',
        // ... other fields
    ];

    public function userCompany()
    {
        return $this->belongsTo(UserCompany::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by_user_id = auth()->id();
                $model->updated_by_user_id = auth()->id();
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by_user_id = auth()->id();
            }
        });
    }
}
```

**✅ Checkpoint:** All tenant-scoped models updated.

---

## Phase 3: Create Authorization Layer

### Step 3.1: Create Quotation Policy

```bash
php artisan make:policy QuotationPolicy --model=Quotation
```

**File:** `app/Policies/QuotationPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->belongsToCompany($user->currentCompanyId());
    }

    public function view(User $user, Quotation $quotation)
    {
        return $user->belongsToCompany($quotation->user_company_id);
    }

    public function create(User $user)
    {
        return $user->belongsToCompany($user->currentCompanyId());
    }

    public function update(User $user, Quotation $quotation)
    {
        $companyId = $user->currentCompanyId();
        
        if ($quotation->user_company_id !== $companyId) {
            return false;
        }
        
        $role = $user->roleInCompany($companyId);
        
        // Owner and Admin can edit all
        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }
        
        // Employees can only edit their own
        if ($role === 'employee' && $quotation->created_by_user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    public function delete(User $user, Quotation $quotation)
    {
        $companyId = $user->currentCompanyId();
        
        if ($quotation->user_company_id !== $companyId) {
            return false;
        }
        
        return $user->hasRoleInCompany($companyId, ['owner', 'admin']);
    }
}
```

**✅ Checkpoint:** QuotationPolicy created.

---

### Step 3.2: Create Bill Policy

```bash
php artisan make:policy BillPolicy --model=Bill
```

Use the same pattern as QuotationPolicy.

---

### Step 3.3: Create Challan Policy

```bash
php artisan make:policy ChallanPolicy --model=Challan
```

Use the same pattern as QuotationPolicy.

---

### Step 3.4: Create Company Member Policy

```bash
php artisan make:policy CompanyMemberPolicy
```

**File:** `app/Policies/CompanyMemberPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyMemberPolicy
{
    use HandlesAuthorization;

    public function inviteMembers(User $user, UserCompany $company)
    {
        return $user->hasRoleInCompany($company->id, ['owner', 'admin']);
    }

    public function removeMember(User $user, UserCompany $company, User $targetUser)
    {
        $userRole = $user->roleInCompany($company->id);
        $targetRole = $targetUser->roleInCompany($company->id);
        
        if ($user->id === $targetUser->id) {
            return false;
        }
        
        if ($targetRole === 'owner') {
            return false;
        }
        
        if ($userRole === 'admin' && $targetRole === 'employee') {
            return true;
        }
        
        if ($userRole === 'owner' && $targetRole !== 'owner') {
            return true;
        }
        
        return false;
    }

    public function changeRole(User $user, UserCompany $company, User $targetUser)
    {
        if (!$user->isOwnerOf($company->id)) {
            return false;
        }
        
        if ($targetUser->roleInCompany($company->id) === 'owner') {
            return false;
        }
        
        return true;
    }
}
```

**✅ Checkpoint:** All policies created.

---

### Step 3.5: Register Policies

**File:** `app/Providers/AuthServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Models\Quotation;
use App\Models\Bill;
use App\Models\Challan;
use App\Models\UserCompany;
use App\Policies\QuotationPolicy;
use App\Policies\BillPolicy;
use App\Policies\ChallanPolicy;
use App\Policies\CompanyMemberPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Quotation::class => QuotationPolicy::class,
        Bill::class => BillPolicy::class,
        Challan::class => ChallanPolicy::class,
        UserCompany::class => CompanyMemberPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
```

**✅ Checkpoint:** Policies registered.

---

## Phase 4: Database Migration

### Step 4.1: Create Migration File

```bash
php artisan make:migration add_multi_user_support_to_system
```

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_add_multi_user_support_to_system.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create company_users pivot table
        Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_company_id')->constrained('user_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->enum('role', ['owner', 'admin', 'employee'])->default('employee');
            $table->boolean('is_active')->default(true);
            $table->timestamp('deactivated_at')->nullable();
            
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['user_company_id', 'user_id', 'deleted_at'], 'company_users_active_unique');
            $table->index(['user_company_id', 'is_active', 'deleted_at'], 'idx_active_company_members');
        });

        // Add user deactivation support
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('current_scope');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
            
            $table->index('is_active');
        });

        // Add creator/updater to quotations
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_company_id')
                  ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')
                  ->constrained('users')->onDelete('set null');
        });

        // Add creator/updater to bills
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_company_id')
                  ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')
                  ->constrained('users')->onDelete('set null');
        });

        // Add creator/updater to challans
        Schema::table('challans', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_company_id')
                  ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')
                  ->constrained('users')->onDelete('set null');
        });

        // Add creator/updater to clients
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_company_id')
                  ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')
                  ->constrained('users')->onDelete('set null');
        });

        // Add creator/updater to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_company_id')
                  ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')
                  ->constrained('users')->onDelete('set null');
        });

        // Add creator/updater to brand_origins
        Schema::table('brand_origins', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_company_id')
                  ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')
                  ->constrained('users')->onDelete('set null');
        });

        // Add creator/updater to product_categories
        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_company_id')
                  ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')
                  ->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'updated_by_user_id']);
        });

        Schema::table('brand_origins', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'updated_by_user_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'updated_by_user_id']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'updated_by_user_id']);
        });

        Schema::table('challans', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'updated_by_user_id']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'updated_by_user_id']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'updated_by_user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'deactivated_at']);
        });

        Schema::dropIfExists('company_users');
    }
};
```

**✅ Checkpoint:** Migration file created.

---

### Step 4.2: Create Data Migration Seeder

```bash
php artisan make:seeder MigrateExistingOwnersSeeder
```

**File:** `database/seeders/MigrateExistingOwnersSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateExistingOwnersSeeder extends Seeder
{
    public function run()
    {
        // Migrate existing owners to company_users
        DB::statement("
            INSERT INTO company_users 
              (user_company_id, user_id, role, is_active, joined_at, invited_at, created_at, updated_at)
            SELECT 
              uc.id as user_company_id,
              uc.owner_id as user_id,
              'owner' as role,
              TRUE as is_active,
              uc.created_at as joined_at,
              uc.created_at as invited_at,
              NOW() as created_at,
              NOW() as updated_at
            FROM user_companies uc
            WHERE NOT EXISTS (
              SELECT 1 FROM company_users cu 
              WHERE cu.user_company_id = uc.id AND cu.user_id = uc.owner_id
            )
        ");

        // Backfill created_by for quotations
        DB::statement("
            UPDATE quotations q
            INNER JOIN user_companies uc ON q.user_company_id = uc.id
            SET q.created_by_user_id = uc.owner_id,
                q.updated_by_user_id = uc.owner_id
            WHERE q.created_by_user_id IS NULL
        ");

        // Backfill created_by for bills
        DB::statement("
            UPDATE bills b
            INNER JOIN user_companies uc ON b.user_company_id = uc.id
            SET b.created_by_user_id = uc.owner_id,
                b.updated_by_user_id = uc.owner_id
            WHERE b.created_by_user_id IS NULL
        ");

        // Backfill created_by for challans
        DB::statement("
            UPDATE challans c
            INNER JOIN user_companies uc ON c.user_company_id = uc.id
            SET c.created_by_user_id = uc.owner_id,
                c.updated_by_user_id = uc.owner_id
            WHERE c.created_by_user_id IS NULL
        ");

        // Backfill created_by for clients
        DB::statement("
            UPDATE clients cl
            INNER JOIN user_companies uc ON cl.user_company_id = uc.id
            SET cl.created_by_user_id = uc.owner_id,
                cl.updated_by_user_id = uc.owner_id
            WHERE cl.created_by_user_id IS NULL
        ");

        // Backfill created_by for products
        DB::statement("
            UPDATE products p
            INNER JOIN user_companies uc ON p.user_company_id = uc.id
            SET p.created_by_user_id = uc.owner_id,
                p.updated_by_user_id = uc.owner_id
            WHERE p.created_by_user_id IS NULL
        ");

        // Backfill created_by for brand_origins
        DB::statement("
            UPDATE brand_origins bo
            INNER JOIN user_companies uc ON bo.user_company_id = uc.id
            SET bo.created_by_user_id = uc.owner_id,
                bo.updated_by_user_id = uc.owner_id
            WHERE bo.created_by_user_id IS NULL
        ");

        // Backfill created_by for product_categories
        DB::statement("
            UPDATE product_categories pc
            INNER JOIN user_companies uc ON pc.user_company_id = uc.id
            SET pc.created_by_user_id = uc.owner_id,
                pc.updated_by_user_id = uc.owner_id
            WHERE pc.created_by_user_id IS NULL
        ");
    }
}
```

**✅ Checkpoint:** Data migration seeder created.

---

### Step 4.3: Test Migration on Staging

```bash
# Switch to staging database
cp .env .env.backup
cp .env.staging .env

# Run migration
php artisan migrate

# Run data migration
php artisan db:seed --class=MigrateExistingOwnersSeeder

# Verify results
php artisan tinker
```

In tinker:
```php
// Check company_users table
DB::table('company_users')->count();
DB::table('company_users')->where('role', 'owner')->count();

// Check if quotations have created_by set
DB::table('quotations')->whereNull('created_by_user_id')->count(); // Should be 0

// Exit tinker
exit;
```

**✅ Checkpoint:** Migration successful on staging.

---

### Step 4.4: Restore Environment

```bash
# Restore original .env
cp .env.backup .env
rm .env.backup
```

---

## Phase 5: Update Controllers

### Step 5.1: Register Middleware

**File:** `app/Http/Kernel.php`

Add to `$middlewareAliases`:

```php
protected $middlewareAliases = [
    // ... existing middleware
    'tenant' => \App\Http\Middleware\SetTenantContext::class,
];
```

Or add to `web` middleware group:

```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\SetTenantContext::class,
    ],
];
```

**✅ Checkpoint:** Middleware registered.

---

### Step 5.2: Create Company Member Controller

```bash
php artisan make:controller CompanyMemberController
```

**File:** `app/Http/Controllers/CompanyMemberController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCompany;
use App\Services\CompanyMemberService;
use Illuminate\Http\Request;

class CompanyMemberController extends Controller
{
    protected $memberService;
    
    public function __construct(CompanyMemberService $memberService)
    {
        $this->memberService = $memberService;
    }
    
    public function index()
    {
        $company = auth()->user()->currentCompany;
        
        $members = $company->members()
            ->withPivot(['role', 'is_active', 'invited_by', 'joined_at', 'deactivated_at'])
            ->get();
        
        return view('company.members.index', compact('members', 'company'));
    }
    
    public function invite(Request $request)
    {
        $company = auth()->user()->currentCompany;
        
        $this->authorize('inviteMembers', $company);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,employee',
        ]);
        
        try {
            $user = $this->memberService->inviteUser(
                $company,
                $validated,
                auth()->user()
            );
            
            return redirect()->route('company.members.index')
                ->with('success', "Invitation sent to {$user->email}");
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function deactivate(User $user)
    {
        $company = auth()->user()->currentCompany;
        
        $this->authorize('removeMember', [$company, $user]);
        
        try {
            $this->memberService->deactivateMember($company, $user);
            
            return redirect()->route('company.members.index')
                ->with('success', "{$user->name} has been deactivated.");
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function reactivate(User $user)
    {
        $company = auth()->user()->currentCompany;
        
        $this->authorize('inviteMembers', $company);
        
        try {
            $this->memberService->reactivateMember($company, $user);
            
            return redirect()->route('company.members.index')
                ->with('success', "{$user->name} has been reactivated.");
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function changeRole(Request $request, User $user)
    {
        $company = auth()->user()->currentCompany;
        
        $this->authorize('changeRole', [$company, $user]);
        
        $validated = $request->validate([
            'role' => 'required|in:admin,employee',
        ]);
        
        try {
            $this->memberService->changeRole($company, $user, $validated['role']);
            
            return redirect()->route('company.members.index')
                ->with('success', "Role updated successfully.");
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

**✅ Checkpoint:** CompanyMemberController created.

---

### Step 5.3: Update Existing Controllers

Update your existing controllers to use authorization. Here's an example with QuotationController:

**File:** `app/Http/Controllers/QuotationController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Client;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Quotation::class);
        
        // Automatically scoped to current company via TenantScope
        $quotations = Quotation::with(['client', 'creator', 'updater'])
            ->latest()
            ->paginate(20);
        
        return view('quotations.index', compact('quotations'));
    }
    
    public function create()
    {
        $this->authorize('create', Quotation::class);
        
        // Clients also automatically scoped
        $clients = Client::orderBy('name')->get();
        
        return view('quotations.create', compact('clients'));
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Quotation::class);
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_no' => 'required|unique:quotations',
            'quotation_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected',
            'notes' => 'nullable|string',
        ]);
        
        // user_company_id and created_by_user_id set automatically
        $quotation = Quotation::create($validated);
        
        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation created successfully.');
    }
    
    public function show(Quotation $quotation)
    {
        $this->authorize('view', $quotation);
        
        $quotation->load(['client', 'creator', 'updater']);
        
        return view('quotations.show', compact('quotation'));
    }
    
    public function edit(Quotation $quotation)
    {
        $this->authorize('update', $quotation);
        
        $clients = Client::orderBy('name')->get();
        
        return view('quotations.edit', compact('quotation', 'clients'));
    }
    
    public function update(Request $request, Quotation $quotation)
    {
        $this->authorize('update', $quotation);
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected',
            'notes' => 'nullable|string',
        ]);
        
        // updated_by_user_id set automatically
        $quotation->update($validated);
        
        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }
    
    public function destroy(Quotation $quotation)
    {
        $this->authorize('delete', $quotation);
        
        $quotation->delete();
        
        return redirect()->route('quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }
}
```

**Apply the same pattern to:**
- BillController
- ChallanController
- ClientController
- ProductController

**✅ Checkpoint:** Controllers updated with authorization.

---

### Step 5.4: Add Routes

**File:** `routes/web.php`

```php
// Company Member Management
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/company/members', [CompanyMemberController::class, 'index'])->name('company.members.index');
    Route::post('/company/members/invite', [CompanyMemberController::class, 'invite'])->name('company.members.invite');
    Route::post('/company/members/{user}/deactivate', [CompanyMemberController::class, 'deactivate'])->name('company.members.deactivate');
    Route::post('/company/members/{user}/reactivate', [CompanyMemberController::class, 'reactivate'])->name('company.members.reactivate');
    Route::post('/company/members/{user}/change-role', [CompanyMemberController::class, 'changeRole'])->name('company.members.changeRole');
});
```

**✅ Checkpoint:** Routes added.

---

## Phase 6: Testing & Validation

### Step 6.1: Create Test Cases

```bash
php artisan make:test TenantSecurityTest
```

**File:** `tests/Feature/TenantSecurityTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCompany;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSecurityTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function user_cannot_see_quotations_from_other_companies()
    {
        // Arrange
        $companyA = UserCompany::factory()->create();
        $userA = User::factory()->create(['current_user_company_id' => $companyA->id]);
        $companyA->members()->attach($userA->id, ['role' => 'owner', 'is_active' => true]);
        
        $quotationA = Quotation::factory()->create([
            'user_company_id' => $companyA->id,
            'created_by_user_id' => $userA->id,
        ]);
        
        $companyB = UserCompany::factory()->create();
        $userB = User::factory()->create(['current_user_company_id' => $companyB->id]);
        $companyB->members()->attach($userB->id, ['role' => 'owner', 'is_active' => true]);
        
        $quotationB = Quotation::factory()->create([
            'user_company_id' => $companyB->id,
            'created_by_user_id' => $userB->id,
        ]);
        
        // Act
        $this->actingAs($userA);
        $quotations = Quotation::all();
        
        // Assert
        $this->assertCount(1, $quotations);
        $this->assertEquals($quotationA->id, $quotations->first()->id);
    }
    
    /** @test */
    public function employee_cannot_edit_other_employees_quotations()
    {
        $company = UserCompany::factory()->create();
        
        $employee1 = User::factory()->create(['current_user_company_id' => $company->id]);
        $company->members()->attach($employee1->id, ['role' => 'employee', 'is_active' => true]);
        
        $employee2 = User::factory()->create(['current_user_company_id' => $company->id]);
        $company->members()->attach($employee2->id, ['role' => 'employee', 'is_active' => true]);
        
        $quotation = Quotation::factory()->create([
            'user_company_id' => $company->id,
            'created_by_user_id' => $employee1->id,
        ]);
        
        $this->actingAs($employee2);
        
        $this->assertFalse($employee2->can('update', $quotation));
    }
}
```

**✅ Checkpoint:** Test cases created.

---

### Step 6.2: Run Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TenantSecurityTest
```

**✅ Checkpoint:** Tests passing.

---

## Phase 7: Production Deployment

### Step 7.1: Pre-Deployment Checklist

- [ ] All code committed to version control
- [ ] Database backup verified
- [ ] Staging tests successful
- [ ] Team notified of maintenance window
- [ ] Rollback plan reviewed

---

### Step 7.2: Deployment Steps

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Run migrations
php artisan migrate --force

# 6. Run data migration
php artisan db:seed --class=MigrateExistingOwnersSeeder --force

# 7. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Bring application back online
php artisan up
```

**✅ Checkpoint:** Application deployed.

---

### Step 7.3: Post-Deployment Verification

```bash
# Check database state
php artisan tinker
```

In tinker:
```php
// Verify company_users populated
DB::table('company_users')->count();

// Verify quotations have creators
DB::table('quotations')->whereNull('created_by_user_id')->count();

// Test a user
$user = User::first();
$user->activeCompanies()->count();
$user->roleInCompany($user->current_user_company_id);

exit;
```

**Manual Testing:**
1. Login as existing user
2. Verify you can see quotations
3. Create a new quotation
4. Verify created_by is set correctly
5. Try to invite a new employee
6. Verify employee can login
7. Verify employee sees only company data
8. Test deactivating employee
9. Verify deactivated employee cannot access

**✅ Checkpoint:** System verified working.

---

## Phase 8: Post-Deployment Monitoring

### Step 8.1: Monitor Logs

```bash
# Watch error logs
tail -f storage/logs/laravel.log

# Check for any authorization errors
grep "Unauthorized" storage/logs/laravel.log

# Check for tenant scope issues
grep "TenantScope" storage/logs/laravel.log
```

---

### Step 8.2: Monitor Database

```sql
-- Check for any NULL created_by_user_id in new records
SELECT COUNT(*) FROM quotations 
WHERE created_at > '2026-02-05' AND created_by_user_id IS NULL;

-- Check company_users growth
SELECT COUNT(*), role FROM company_users 
WHERE is_active = TRUE 
GROUP BY role;
```

---

### Step 8.3: User Feedback

- Monitor support tickets for issues
- Check for any reports of missing data
- Verify no cross-company data visibility

---

## Rollback Procedures

### If Issues Arise

```bash
# 1. Put in maintenance mode
php artisan down

# 2. Rollback migration
php artisan migrate:rollback

# 3. Restore code from backup
git checkout [previous-commit-hash]

# 4. Clear caches
php artisan config:clear
php artisan cache:clear

# 5. Bring back online
php artisan up
```

### If Database Corrupted

```bash
# Restore from backup
mysql -u your_username -p beayar < backup_beayar_[timestamp].sql
```

---

## Success Metrics

After deployment, verify:

✅ **Security:**
- [ ] Users cannot see other companies' data
- [ ] Employee permissions work correctly
- [ ] Deactivated users cannot access resources

✅ **Functionality:**
- [ ] All CRUD operations work
- [ ] Employee invitations work
- [ ] Role changes work
- [ ] Audit trails preserved

✅ **Performance:**
- [ ] Page load times acceptable
- [ ] Queries using proper indexes
- [ ] No N+1 query issues

---

## Support & Troubleshooting

### Common Issues

**Issue:** User sees "You do not have access to any company"
**Solution:** Check company_users table, ensure user has active membership

**Issue:** Created_by_user_id is NULL on new records
**Solution:** Verify user is authenticated when creating, check model boot method

**Issue:** Employee can see other companies' data
**Solution:** Verify BelongsToTenant trait is on model, check current_user_company_id is set

**Issue:** Authorization failures
**Solution:** Check policies are registered, verify roleInCompany() returns correct value

---

## Completion Checklist

- [ ] Phase 0: Backups completed
- [ ] Phase 1: Infrastructure code created
- [ ] Phase 2: Models updated
- [ ] Phase 3: Policies created
- [ ] Phase 4: Database migrated
- [ ] Phase 5: Controllers updated
- [ ] Phase 6: Tests passing
- [ ] Phase 7: Deployed to production
- [ ] Phase 8: Monitoring active

**🎉 Congratulations! Your multi-user SaaS system is now secure and production-ready!**

---

## Next Steps

1. **Add UI for company switching** - Allow users in multiple companies to switch context
2. **Email notifications** - Set up invitation and deactivation emails
3. **Activity logging** - Enhance audit trails with detailed activity logs
4. **Reporting** - Build reports showing who created what
5. **Subscription enforcement** - Limit features based on subscription plan

---

## Documentation

Keep this guide for future reference and onboarding new developers.

**Last Updated:** February 5, 2026  
**Version:** 2.0  
**Author:** System Administrator
