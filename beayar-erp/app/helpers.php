<?php

if (!function_exists('currentTenantId')) {
    /**
     * Get the current tenant company ID.
     *
     * @return int|null
     */
    function currentTenantId(): ?int
    {
        // Get from session (set by tenant.context middleware)
        $tenantId = session('tenant_company_id');
        
        if ($tenantId) {
            return (int) $tenantId;
        }
        
        // Fallback to user's current tenant
        $user = auth()->user();
        if ($user && isset($user->current_tenant_company_id)) {
            return (int) $user->current_tenant_company_id;
        }
        
        return null;
    }
}

if (!function_exists('currentTenant')) {
    /**
     * Get the current tenant company model.
     *
     * @return \App\Models\TenantCompany|null
     */
    function currentTenant(): ?\App\Models\TenantCompany
    {
        $id = currentTenantId();
        if (!$id) {
            return null;
        }
        
        return \App\Models\TenantCompany::find($id);
    }
}

if (!function_exists('tenant')) {
    /**
     * Alias for currentTenant().
     *
     * @return \App\Models\TenantCompany|null
     */
    function tenant(): ?\App\Models\TenantCompany
    {
        return currentTenant();
    }
}
