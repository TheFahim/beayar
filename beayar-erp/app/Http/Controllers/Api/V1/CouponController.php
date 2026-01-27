<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coupon\CouponCreateRequest;
use App\Models\Coupon;
use App\Services\Tenant\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function index(): JsonResponse
    {
        // Tenant might see coupons they created or global ones available to them?
        // Usually tenants don't see all global coupons unless applying.
        // Let's assume this lists coupons created by the tenant (if feature exists)
        return response()->json(Coupon::where('type', 'unique')->paginate(20));
    }

    public function store(CouponCreateRequest $request): JsonResponse
    {
        $coupon = Coupon::create(array_merge(
            $request->validated(),
            ['type' => 'unique'] // Tenants create unique coupons usually
        ));
        return response()->json($coupon, 201);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        
        $coupon = $this->couponService->validateCoupon($request->code, $request->user());
        
        if (!$coupon) {
            return response()->json(['message' => 'Invalid or expired coupon'], 422);
        }

        return response()->json($coupon);
    }
}
