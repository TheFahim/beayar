<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CompanyCreateRequest;
use App\Models\CustomerCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Assuming we are listing CustomerCompanies (sub-companies)
        $companies = CustomerCompany::paginate(20);
        return response()->json($companies);
    }

    public function store(CompanyCreateRequest $request): JsonResponse
    {
        $company = CustomerCompany::create(array_merge(
            $request->validated(),
            ['user_company_id' => $request->user()->current_user_company_id]
        ));

        return response()->json($company, 201);
    }

    public function show(CustomerCompany $company): JsonResponse
    {
        return response()->json($company);
    }

    public function update(CompanyCreateRequest $request, CustomerCompany $company): JsonResponse
    {
        $company->update($request->validated());
        return response()->json($company);
    }

    public function destroy(CustomerCompany $company): JsonResponse
    {
        $company->delete();
        return response()->json(['message' => 'Company deleted successfully']);
    }
}
