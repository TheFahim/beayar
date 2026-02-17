<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $allFeatures = Feature::pluck('id', 'slug');

        // Free Plan — core features only
        $freeFeatureSlugs = [
            'dashboard',
            'customers.manage',
            'products.manage',
            'images.library',
            'quotations.create',
            'quotations.edit',
            'billing.create',
            'challans.manage',
            'organization.team_members',
        ];

        // Pro Plan — most features
        $proFeatureSlugs = array_merge($freeFeatureSlugs, [
            'quotations.revisions',
            'quotations.export',
            'billing.advance',
            'billing.running',
            'finance.dashboard',
            'received_bills.manage',
            'organization.multi_company',
            'brand_origins.manage',
        ]);

        // Pro Plus / Custom — all features
        $allFeatureSlugs = $allFeatures->keys()->toArray();

        $planFeatureMap = [
            'free' => $freeFeatureSlugs,
            'pro' => $proFeatureSlugs,
            'pro-plus' => $allFeatureSlugs,
            'custom' => $allFeatureSlugs,
        ];

        foreach ($planFeatureMap as $planSlug => $slugs) {
            $plan = Plan::where('slug', $planSlug)->first();

            if (! $plan) {
                continue;
            }

            $featureIds = $allFeatures->only($slugs)->values()->toArray();
            $plan->features()->syncWithoutDetaching($featureIds);
        }
    }
}
