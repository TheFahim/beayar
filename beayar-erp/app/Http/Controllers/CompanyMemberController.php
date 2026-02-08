<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CompanyMemberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CompanyMemberController extends Controller
{
    protected $memberService;

    public function __construct(CompanyMemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $tenantId = Session::get('tenant_id');
        $company = $user->companies()->where('user_companies.id', $tenantId)->first() 
                   ?? $user->ownedCompanies()->where('id', $tenantId)->first();

        if (!$company) {
            abort(403, 'Company context not found.');
        }

        // Authorize via Policy (checking against the company context)
        // Note: You might want to use a Policy on UserCompany or a custom one.
        // For now, simple check or rely on policy if registered.
        // $this->authorize('viewAny', [User::class, $company]);

        $members = $company->members()->get();
        // Include owner in the list for display if needed, or separate.
        $owner = $company->owner;

        return view('company_members.index', compact('company', 'members', 'owner'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:company_admin,employee',
        ]);

        $user = Auth::user();
        $tenantId = Session::get('tenant_id');
        $company = $user->companies()->where('user_companies.id', $tenantId)->first() 
                   ?? $user->ownedCompanies()->where('id', $tenantId)->first();

        if (!$company) {
            abort(403);
        }
        
        // Use Policy to check creation rights
        // $this->authorize('create', [User::class, $company]);

        try {
            $this->memberService->addMember($company, $request->email, $request->role);
            return redirect()->back()->with('success', 'Member added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'role' => 'required|in:company_admin,employee',
        ]);

        $targetUser = User::findOrFail($id);
        $user = Auth::user();
        $tenantId = Session::get('tenant_id');
        $company = $user->companies()->where('user_companies.id', $tenantId)->first() 
                   ?? $user->ownedCompanies()->where('id', $tenantId)->first();

        if (!$company) {
            abort(403);
        }

        // $this->authorize('update', [User::class, $company]);

        try {
            $this->memberService->updateRole($company, $targetUser, $request->role);
            return redirect()->back()->with('success', 'Member role updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $targetUser = User::findOrFail($id);
        $user = Auth::user();
        $tenantId = Session::get('tenant_id');
        $company = $user->companies()->where('user_companies.id', $tenantId)->first() 
                   ?? $user->ownedCompanies()->where('id', $tenantId)->first();

        if (!$company) {
            abort(403);
        }

        // $this->authorize('delete', [User::class, $company]);

        try {
            $this->memberService->removeMember($company, $targetUser);
            return redirect()->back()->with('success', 'Member removed.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
