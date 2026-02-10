<?php

namespace App\Services\Tenant;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\PlatformInvoice;
use App\Models\User;

class CouponService
{
    public function validateCoupon(string $code, User $user): ?Coupon
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return null; // Invalid
        }

        // Check expiry, usage limits, etc.
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return null; // Expired
        }

        return $coupon;
    }

    public function applyCoupon(Coupon $coupon, PlatformInvoice $invoice)
    {
        $discount = 0;
        if ($coupon->discount_amount) {
            $discount = $coupon->discount_amount;
        } elseif ($coupon->discount_percentage) {
            $discount = ($invoice->subtotal * $coupon->discount_percentage) / 100;
        }

        $invoice->update([
            'discount' => $discount,
            'total' => $invoice->subtotal - $discount,
        ]);

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $invoice->user_id,
            'platform_invoice_id' => $invoice->id,
            'discount_applied' => $discount,
            'used_at' => now(),
        ]);
    }
}
