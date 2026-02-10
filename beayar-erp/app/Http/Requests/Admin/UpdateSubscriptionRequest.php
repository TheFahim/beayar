<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
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
            'plan_id' => 'nullable|exists:plans,id',
            'status' => 'nullable|in:active,cancelled,expired,trial',
            'custom_limits' => 'nullable|array',
            'custom_limits.sub_companies' => 'nullable|integer|min:-1',
            'custom_limits.quotations' => 'nullable|integer|min:-1',
            'custom_limits.employees' => 'nullable|integer|min:-1',
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
            'plan_id.exists' => 'The selected plan does not exist.',
            'module_access.*.exists' => 'One or more selected modules are invalid.',
        ];
    }
}
