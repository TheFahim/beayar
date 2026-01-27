<?php

namespace App\Services\Tenant;

use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function createQuotation(User $user, array $data): Quotation
    {
        return DB::transaction(function () use ($user, $data) {
            $quotation = Quotation::create([
                'user_company_id' => $user->current_user_company_id,
                'customer_id' => $data['customer_id'],
                'user_id' => $user->id,
                'status_id' => $data['status_id'],
                'po_no' => $data['po_no'] ?? null,
                'ship_to' => $data['ship_to'] ?? null,
            ]);

            $this->createRevision($quotation, $data, $user);

            return $quotation;
        });
    }

    public function createRevision(Quotation $quotation, array $data, User $user): QuotationRevision
    {
        $revision = $quotation->revisions()->create([
            'revision_no' => $data['revision_no'] ?? 'R' . ($quotation->revisions()->count()),
            'date' => now(),
            'subtotal' => $data['subtotal'],
            'total' => $data['total'],
            'created_by' => $user->id,
            'is_active' => true,
            // Add other fields
        ]);

        // Disable other revisions if this is active
        if ($revision->is_active) {
            $quotation->revisions()->where('id', '!=', $revision->id)->update(['is_active' => false]);
        }

        // Add Products
        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $revision->products()->create($product);
            }
        }

        return $revision;
    }
}
