<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string|max:1000',
            'base_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'is_active' => 'boolean',
            'limits' => 'nullable|array',
            'limits.sub_companies' => 'nullable|integer|min:-1',
            'limits.quotations' => 'nullable|integer|min:-1',
            'limits.employees' => 'nullable|integer|min:-1',
            'module_access' => 'nullable|array',
            'module_access.*' => 'string|exists:modules,slug',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:features,id',
            'feature_limits' => 'nullable|array',
            'feature_limits.*' => 'nullable|integer|min:-1',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'This plan slug is already in use.',
            'module_access.*.exists' => 'One or more selected modules are invalid.',
        ];
    }
}
