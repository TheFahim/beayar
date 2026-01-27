<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TenantSuspendRequest;
use App\Models\UserCompany;
use App\Services\SuperAdmin\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantManagementController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index(): JsonResponse
    {
        $tenants = UserCompany::with(['owner', 'subscription.plan'])->paginate(20);
        return response()->json($tenants);
    }

    public function show(UserCompany $company): JsonResponse
    {
        $company->load(['owner', 'subscription.plan', 'users']);
        return response()->json($company);
    }

    public function suspend(TenantSuspendRequest $request, UserCompany $company): JsonResponse
    {
        // Implementation for suspending tenant
        // This would likely update a status field on UserCompany or User
        $company->update(['status' => 'suspended']);
        
        // Log the suspension reason...
        
        return response()->json(['message' => 'Tenant suspended successfully']);
    }

    public function impersonate(UserCompany $company): JsonResponse
    {
        $owner = $company->owner;
        $this->adminService->impersonateTenant($owner);
        
        // In API context, we might return a temporary token for the user
        $token = $owner->createToken('impersonation-token')->plainTextToken;

        return response()->json([
            'message' => 'Impersonation started',
            'token' => $token,
            'user' => $owner
        ]);
    }
}
