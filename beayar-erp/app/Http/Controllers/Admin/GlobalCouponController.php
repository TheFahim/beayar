<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GlobalCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;

class GlobalCouponController extends Controller
{
    public function index(): JsonResponse
    {
        $coupons = Coupon::where('type', 'campaign')->latest()->paginate(20);

        return response()->json($coupons);
    }

    public function store(GlobalCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::create(array_merge(
            $request->validated(),
            ['type' => 'campaign']
        ));

        return response()->json($coupon, 201);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }
}
