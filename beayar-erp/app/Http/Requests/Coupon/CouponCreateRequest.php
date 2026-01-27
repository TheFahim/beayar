<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class CouponCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only allow if user can create coupons (e.g. platform admin or feature flag)
        // For now, assume tenant admins can create coupons for their customers if feature enabled
        return true; 
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'unique:coupons,code'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_spend' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
