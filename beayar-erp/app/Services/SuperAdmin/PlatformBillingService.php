<?php

namespace App\Services\SuperAdmin;

use App\Models\PlatformInvoice;
use App\Models\PlatformPayment;
use App\Models\Subscription;
use App\Models\User;

class PlatformBillingService
{
    public function generateInvoice(Subscription $subscription): PlatformInvoice
    {
        $plan = $subscription->plan;
        
        $invoice = PlatformInvoice::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'subtotal' => $plan->base_price,
            'tax' => 0, // Implement tax logic
            'discount' => 0, // Implement discount logic
            'total' => $plan->base_price,
            'status' => 'pending',
            'due_date' => now()->addDays(7),
        ]);

        return $invoice;
    }

    public function recordPayment(PlatformInvoice $invoice, string $transactionId, string $provider, array $details): PlatformPayment
    {
        $payment = PlatformPayment::create([
            'platform_invoice_id' => $invoice->id,
            'transaction_id' => $transactionId,
            'provider' => $provider,
            'amount' => $invoice->total,
            'currency' => 'USD',
            'status' => 'succeeded',
            'payment_method_details' => $details,
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $payment;
    }
}
