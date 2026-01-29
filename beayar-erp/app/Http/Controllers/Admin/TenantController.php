<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserCompany;
use App\Services\SuperAdmin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index(): View
    {
        $tenants = UserCompany::with(['owner.subscription.plan'])->latest()->paginate(10);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function suspend(Request $request, UserCompany $company): RedirectResponse
    {
        // Toggle suspension logic - reusing basic logic for now, ideally should use a Service
        // Assuming 'status' column or similar. If not present, we might need to add it or use a different flag.
        // The TenantManagementController used $company->update(['status' => 'suspended']);
        
        $newStatus = $request->input('status', 'suspended');
        $company->update(['status' => $newStatus]);

        return back()->with('success', 'Tenant status updated successfully.');
    }

    public function impersonate(UserCompany $company): RedirectResponse
    {
        $owner = $company->owner;
        $this->adminService->impersonateTenant($owner);

        return redirect()->route('tenant.dashboard')->with('success', 'Impersonating ' . $company->name);
    }
}
