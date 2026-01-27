<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $guarded = ['id'];
    
    // Helper to check if model uses the company scope
    public function isTenantScoped(): bool
    {
        return in_array(\App\Traits\BelongsToCompany::class, class_uses_recursive($this));
    }
}
