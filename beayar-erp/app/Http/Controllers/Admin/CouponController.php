<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GlobalCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::where('type', 'campaign')->latest()->paginate(10);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(GlobalCouponRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $data = [
            'code' => $validated['code'],
            'type' => 'campaign',
            'start_date' => now(), // Default to now
            'end_date' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ];

        if ($validated['discount_type'] === 'percentage') {
            $data['discount_percentage'] = $validated['discount_value'];
            $data['discount_amount'] = 0;
        } else {
            $data['discount_amount'] = $validated['discount_value'];
            $data['discount_percentage'] = 0;
        }

        $coupon = Coupon::create($data);
        
        activity()
           ->performedOn($coupon)
           ->causedBy(auth()->guard('admin')->user())
           ->log('created coupon');

        return back()->with('success', 'Coupon created successfully.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        activity()
           ->performedOn($coupon)
           ->causedBy(auth()->guard('admin')->user())
           ->log('deleted coupon');
           
        $coupon->delete();

        return back()->with('success', 'Coupon deleted successfully.');
    }
}
