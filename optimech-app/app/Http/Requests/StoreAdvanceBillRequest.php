<?php

namespace App\Http\Requests;

use App\Models\Bill;
use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdvanceBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $quotation = $this->route('quotation');
        $billType = $this->input('bill_type');

        $base = [
            'bill_type' => ['required', Rule::in(['advance', 'running'])],
            'invoice_no' => ['required', Rule::unique('bills', 'invoice_no')],
            'bill_date' => ['required', 'date_format:d/m/Y'],
            'payment_received_date' => ['nullable', 'date_format:d/m/Y'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($billType === 'running') {
            return array_merge($base, [
                'quotation_id' => ['required', 'integer', Rule::in([$quotation?->id])],
                'parent_bill_id' => ['required', 'integer', 'exists:bills,id'],
                'installment_amount' => ['required', 'numeric', 'min:0'],
                'installment_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            ]);
        }

        return array_merge($base, [
            'quotation_id' => ['required', 'integer', Rule::in([$quotation?->id])],
            'quotation_revision_id' => ['required', 'integer'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'po_no' => ['required', 'string', 'max:255'],
            'bill_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'due' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /**
     * Validation rules for advance bills.
     */
    protected function advanceBillRules(): array
    {
        return [
            'bill_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'due' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Validation rules for regular bills.
     */
    protected function regularBillRules(): array
    {
        return [];
    }

    /**
     * Validation rules for running bills.
     */
    protected function runningBillRules(): array
    {
        return [];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $billType = $this->input('bill_type');

            if ($billType === 'advance') {
                $this->validateAdvanceBill($validator);
            }

            if ($billType === 'running') {
                $this->validateRunningBill($validator);
            }

            // Prevent bill creation for draft quotations (active revision must be saved as 'quotation')
            $quotation = $this->route('quotation');
            if ($quotation) {
                $activeRevision = $quotation->revisions()->where('is_active', 1)->first();
                if (! $activeRevision || ($activeRevision->saved_as ?? null) !== 'quotation') {
                    $validator->errors()->add('quotation_id', 'Bills cannot be created from draft quotations. Activate the quotation revision to proceed.');
                }
            }
        });
    }

    /**
     * Additional validation for advance bills.
     */
    protected function validateAdvanceBill($validator)
    {
        $quotation = $this->quotation;
        $advancePercentage = $this->input('advance_percentage');

        // Cannot create advance bill if challans already exist for this quotation
        $hasChallans = \App\Models\Challan::whereHas('revision', function ($query) use ($quotation) {
            $query->where('quotation_id', $quotation->id)->where('is_active', 1);
        })->exists();

        if ($hasChallans) {
            $validator->errors()->add('bill_type', 'Cannot create advance bill - challans already exist for this quotation.');

            return;
        }

        // Validate no existing advance bills
        $existingAdvanceBills = $quotation->bills()
            ->where('bill_type', 'advance')
            ->count();

        if ($existingAdvanceBills > 0) {
            $validator->errors()->add('bill_type', 'This quotation already has an advance bill. You can view/edit the existing advance bill.');

            return;
        }

        // Removed installment-specific validation
    }

    /**
     * Additional validation for regular bills.
     */
    protected function validateRegularBill($validator) {}

    /**
     * Additional validation for running bills.
     */
    protected function validateRunningBill($validator)
    {
        $parentId = $this->input('parent_bill_id');
        $amount = floatval($this->input('installment_amount'));
        $percentage = floatval($this->input('installment_percentage'));

        $parent = Bill::find($parentId);
        if (! $parent) {
            $validator->errors()->add('parent_bill_id', 'Selected parent bill not found.');

            return;
        }

        if ($parent->bill_type !== 'advance') {
            $validator->errors()->add('parent_bill_id', 'Parent bill must be an advance bill.');

            return;
        }

        if ($parent->quotation_id != $this->route('quotation')?->id) {
            $validator->errors()->add('quotation_id', 'Running bill must have the same quotation as the parent bill.');

            return;
        }

        $billedThroughRunning = $parent->children()->where('bill_type', 'running')->sum('total_amount');
        $remainingAmount = max(0, ($parent->total_amount ?? 0) - $billedThroughRunning);
        $maxPercentage = ($parent->total_amount ?? 0) > 0 ? ($remainingAmount / $parent->total_amount) * 100 : 0;

        if ($amount > $remainingAmount) {
            $validator->errors()->add('installment_amount', 'Installment amount exceeds remaining balance.');
        }

        if ($percentage > min(100, $maxPercentage)) {
            $validator->errors()->add('installment_percentage', 'Running percentage exceeds allowable remaining.');
        }
    }

    /**
     * Validate that installment due dates are sequential.
     */
    // Removed installment sequential date validation

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'bill_type.required' => 'Please select a bill type.',
            'bill_type.in' => 'Invalid bill type selected.',
            'bill_date.required' => 'Please select a bill date.',
            'bill_date.date' => 'Please enter a valid date.',
            'due_date.required' => 'Please select a due date.',
            'due_date.date' => 'Please enter a valid due date.',
            'due_date.after_or_equal' => 'Due date must be on or after the bill date.',
            'discount.numeric' => 'Discount must be a valid number.',
            'discount.min' => 'Discount cannot be negative.',
            'discount.max' => 'Discount amount is too large.',
            'advance_percentage.required' => 'Please enter an advance percentage.',
            'advance_percentage.numeric' => 'Advance percentage must be a valid number.',
            'advance_percentage.min' => 'Advance percentage must be at least 1%.',
            'advance_percentage.max' => 'Advance percentage cannot exceed 100%.',
            'running_percentage.required' => 'Please enter a running percentage.',
            'running_percentage.numeric' => 'Running percentage must be a valid number.',
            'running_percentage.min' => 'Running percentage must be at least 1%.',
            'running_percentage.max' => 'Running percentage cannot exceed 100%.',
            'parent_bill_id.required' => 'Please select a parent advance bill.',
            'parent_bill_id.exists' => 'Selected parent bill not found.',
            // Removed installment-specific error messages
            'challan_products.required' => 'Please select at least one challan product.',
            'challan_products.array' => 'Challan products must be provided as an array.',
            'challan_products.min' => 'Please select at least one challan product.',
            'challan_products.*.challan_product_id.required' => 'Challan product ID is required.',
            'challan_products.*.challan_product_id.exists' => 'Selected challan product not found.',
            'challan_products.*.quantity.required' => 'Product quantity is required.',
            'challan_products.*.quantity.numeric' => 'Product quantity must be a valid number.',
            'challan_products.*.quantity.min' => 'Product quantity must be greater than 0.',
            'challan_products.*.unit_price.required' => 'Unit price is required.',
            'challan_products.*.unit_price.numeric' => 'Unit price must be a valid number.',
            'challan_products.*.unit_price.min' => 'Unit price must be greater than 0.',
            'challan_products.*.tax_percentage.numeric' => 'Tax percentage must be a valid number.',
            'challan_products.*.tax_percentage.min' => 'Tax percentage cannot be negative.',
            'challan_products.*.tax_percentage.max' => 'Tax percentage cannot exceed 100%.',
        ];
    }
}
