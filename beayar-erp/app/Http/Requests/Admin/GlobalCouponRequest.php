<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GlobalCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')->isSuperAdmin();
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
