<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'limits.sub_companies.min' => 'Use -1 for unlimited sub-companies.',
            'limits.quotations.min' => 'Use -1 for unlimited quotations.',
            'limits.employees.min' => 'Use -1 for unlimited employees.',
            'module_access.*.exists' => 'One or more selected modules are invalid.',
        ];
    }
}
