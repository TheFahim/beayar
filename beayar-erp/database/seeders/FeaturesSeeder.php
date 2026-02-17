<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Module;
use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $quotationsModule = Module::where('slug', 'quotations')->first()?->id;
        $billingModule = Module::where('slug', 'billing')->first()?->id;
        $challansModule = Module::where('slug', 'challans')->first()?->id;

        $features = [
            // Core features (no module)
            ['name' => 'Dashboard', 'slug' => 'dashboard', 'module_id' => null, 'sort_order' => 0],
            ['name' => 'Customer Management', 'slug' => 'customers.manage', 'module_id' => null, 'sort_order' => 1],
            ['name' => 'Product Catalog', 'slug' => 'products.manage', 'module_id' => null, 'sort_order' => 2],
            ['name' => 'Image Library', 'slug' => 'images.library', 'module_id' => null, 'sort_order' => 3],

            // Quotation features
            ['name' => 'Create Quotation', 'slug' => 'quotations.create', 'module_id' => $quotationsModule, 'sort_order' => 10],
            ['name' => 'Edit Quotation', 'slug' => 'quotations.edit', 'module_id' => $quotationsModule, 'sort_order' => 11],
            ['name' => 'Quotation Revisions', 'slug' => 'quotations.revisions', 'module_id' => $quotationsModule, 'sort_order' => 12],
            ['name' => 'Export Quotation', 'slug' => 'quotations.export', 'module_id' => $quotationsModule, 'sort_order' => 13],

            // Billing features
            ['name' => 'Create Bill', 'slug' => 'billing.create', 'module_id' => $billingModule, 'sort_order' => 20],
            ['name' => 'Advance Billing', 'slug' => 'billing.advance', 'module_id' => $billingModule, 'sort_order' => 21],
            ['name' => 'Running Bills', 'slug' => 'billing.running', 'module_id' => $billingModule, 'sort_order' => 22],

            // Challan features
            ['name' => 'Challan Management', 'slug' => 'challans.manage', 'module_id' => $challansModule, 'sort_order' => 30],

            // Finance
            ['name' => 'Finance Dashboard', 'slug' => 'finance.dashboard', 'module_id' => null, 'sort_order' => 40],

            // Received Bills
            ['name' => 'Received Bills', 'slug' => 'received_bills.manage', 'module_id' => null, 'sort_order' => 41],

            // Organization
            ['name' => 'Multiple Companies', 'slug' => 'organization.multi_company', 'module_id' => null, 'sort_order' => 50],
            ['name' => 'Team Members', 'slug' => 'organization.team_members', 'module_id' => null, 'sort_order' => 51],
            ['name' => 'Brand Origins', 'slug' => 'brand_origins.manage', 'module_id' => null, 'sort_order' => 52],
        ];

        foreach ($features as $feature) {
            Feature::updateOrCreate(
                ['slug' => $feature['slug']],
                $feature
            );
        }
    }
}
