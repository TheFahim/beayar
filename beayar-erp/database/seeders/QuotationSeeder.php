<?php

namespace Database\Seeders;

use App\Models\BrandOrigin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $tenantCompanies = TenantCompany::all();

        if ($tenantCompanies->isEmpty()) {
            return;
        }

        foreach ($tenantCompanies as $company) {
            $this->seedQuotationsForCompany($company);
        }
    }

    private function seedQuotationsForCompany(TenantCompany $company)
    {
        $customers = Customer::where('tenant_company_id', $company->id)->get();
        $products = Product::where('tenant_company_id', $company->id)->with('specifications')->get();
        $owner = User::find($company->owner_id);
        $statuses = QuotationStatus::all();
        $brandOrigins = BrandOrigin::all();

        if ($customers->isEmpty() || $products->isEmpty() || ! $owner) {
            return;
        }

        // Create 5 quotations per company
        for ($i = 0; $i < 5; $i++) {
            $customer = $customers->random();
            $status = $statuses->random();

            $quotation = Quotation::create([
                'tenant_company_id' => $company->id,
                'customer_id' => $customer->id,
                'user_id' => $owner->id,
                'status_id' => $status->id,
                'quotation_no' => 'QT-'.$company->id.'-'.date('Y').'-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT).'-'.uniqid(),
                'reference_no' => 'REF-'.uniqid(),
                'po_no' => 'PO-'.uniqid(),
                'po_date' => Carbon::now()->subDays(rand(1, 30)),
                'ship_to' => $customer->address,
            ]);

            $this->createRevision($quotation, $products, $owner, $brandOrigins);
        }
    }

    private function createRevision(Quotation $quotation, $products, User $user, $brandOrigins)
    {
        // Create 1-3 revisions
        $revisionCount = rand(1, 3);

        for ($j = 0; $j < $revisionCount; $j++) {
            $isActive = ($j === $revisionCount - 1); // Last one is active

            $subtotal = 0;
            $items = [];

            // Pick random products
            $selectedProducts = $products->random(rand(1, min(3, $products->count())));

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 10);
                $price = rand(100, 1000);
                $total = $qty * $price;
                $subtotal += $total;

                $specification = $product->specifications->isNotEmpty() ? $product->specifications->random() : null;
                $brandOrigin = $brandOrigins->isNotEmpty() ? $brandOrigins->random() : null;

                $items[] = [
                    'product_id' => $product->id,
                    'brand_origin_id' => $brandOrigin?->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $total,
                    'specification_id' => $specification?->id,
                    'unit' => 'pcs',
                ];
            }

            $revision = QuotationRevision::create([
                'quotation_id' => $quotation->id,
                'revision_no' => 'R'.$j,
                'date' => Carbon::now(),
                'valid_until' => Carbon::now()->addDays(15),
                'currency' => 'BDT',
                'subtotal' => $subtotal,
                'total' => $subtotal, // Simplified for seed
                'created_by' => $user->id,
                'is_active' => $isActive,
                'saved_as' => $isActive ? 'quotation' : 'draft',
                'status' => 'draft',
                'terms_conditions' => 'Standard terms and conditions apply.',
            ]);

            foreach ($items as $item) {
                $item['quotation_revision_id'] = $revision->id;
                QuotationProduct::create($item);
            }
        }
    }
}
