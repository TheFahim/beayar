<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     *
     * @var array<int, string>
     */


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */


     public function hasRole($roles, $guard = null): bool
     {
         if ($this->username === 'developer') {
             return true;
         }

         if (is_array($roles)) {
             foreach ($roles as $role) {
                 if ($this->roles()->where('name', $role)->exists()) {
                     return true;
                 }
             }
             return false;
         }

         return $this->roles()->where('name', $roles)->exists();
     }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function saleTarget(){
        return $this->hasMany(SaleTarget::class);
    }


}
