<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmSubscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Get all visible and active plans.
     */
    public function getAvailablePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return SubscriptionPlan::active()
            ->visible()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Subscribe a farm to a plan.
     */
    public function subscribe(
        Farm $farm,
        SubscriptionPlan $plan,
        string $billingCycle = 'annual',
        ?int $createdBy = null
    ): FarmSubscription {
        return DB::transaction(function () use ($farm, $plan, $billingCycle, $createdBy) {
            // Cancel any existing active subscription
            $farm->subscriptions()->active()->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Upgraded/Changed plan',
            ]);

            $price = $plan->getPriceForCycle($billingCycle);
            $startsAt = now();
            $endsAt = $billingCycle === 'annual'
                ? now()->addYear()
                : now()->addMonth();

            // Create subscription
            $subscription = FarmSubscription::create([
                'farm_id' => $farm->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'price' => $price,
                'status' => $plan->isFree() ? 'active' : 'pending',
                'starts_at' => $plan->isFree() ? $startsAt : null,
                'ends_at' => $plan->isFree() ? $endsAt : null,
                'auto_renew' => true,
                'created_by' => $createdBy,
            ]);

            // Create invoice for paid plans
            if (!$plan->isFree()) {
                $this->createInvoice($subscription, $createdBy);
            }

            return $subscription;
        });
    }

    /**
     * Create an invoice for a subscription.
     */
    public function createInvoice(
        FarmSubscription $subscription,
        ?int $createdBy = null
    ): SubscriptionInvoice {
        $plan = $subscription->plan;
        $subtotal = $subscription->price;

        // Calculate discount for annual plans
        $discount = 0;
        if ($subscription->billing_cycle === 'annual') {
            $monthlyTotal = $plan->monthly_price * 12;
            $discount = $monthlyTotal - $subtotal;
        }

        return SubscriptionInvoice::create([
            'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
            'farm_id' => $subscription->farm_id,
            'subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'subtotal' => $plan->monthly_price * ($subscription->billing_cycle === 'annual' ? 12 : 1),
            'discount' => $discount,
            'tax' => 0,
            'total' => $subtotal,
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'created_by' => $createdBy,
            'metadata' => [
                'plan_name' => $plan->name,
                'billing_cycle' => $subscription->billing_cycle,
            ],
        ]);
    }

    /**
     * Activate a subscription after payment.
     */
    public function activateSubscription(FarmSubscription $subscription): void
    {
        $endsAt = $subscription->billing_cycle === 'annual'
            ? now()->addYear()
            : now()->addMonth();

        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => $endsAt,
        ]);
    }

    /**
     * Process invoice payment.
     */
    public function processPayment(
        SubscriptionInvoice $invoice,
        string $paymentMethod,
        ?string $reference = null,
        ?string $proofPath = null,
        ?int $paidBy = null
    ): void {
        DB::transaction(function () use ($invoice, $paymentMethod, $reference, $proofPath, $paidBy) {
            $invoice->markAsPaid($paymentMethod, $reference, $proofPath, $paidBy);
            $this->activateSubscription($invoice->subscription);
        });
    }

    /**
     * Get pending invoices for a farm.
     */
    public function getPendingInvoices(Farm $farm): \Illuminate\Database\Eloquent\Collection
    {
        return $farm->subscriptionInvoices()
            ->pending()
            ->with(['plan', 'subscription'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get all invoices for a farm.
     */
    public function getInvoices(Farm $farm): \Illuminate\Database\Eloquent\Collection
    {
        return $farm->subscriptionInvoices()
            ->with(['plan', 'subscription'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get payment history for a farm.
     */
    public function getPaymentHistory(Farm $farm): \Illuminate\Database\Eloquent\Collection
    {
        return $farm->subscriptionInvoices()
            ->paid()
            ->with(['plan', 'subscription', 'paidBy'])
            ->orderByDesc('paid_at')
            ->get();
    }

    /**
     * Check if farm can upgrade to a plan.
     */
    public function canUpgrade(Farm $farm, SubscriptionPlan $targetPlan): bool
    {
        $currentPlan = $farm->getCurrentPlan();

        if (!$currentPlan) {
            return true;
        }

        return $targetPlan->sort_order > $currentPlan->sort_order;
    }

    /**
     * Check for expired subscriptions and mark them.
     */
    public function checkExpiredSubscriptions(): int
    {
        return FarmSubscription::active()
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Renew a subscription.
     */
    public function renewSubscription(FarmSubscription $subscription, ?int $createdBy = null): FarmSubscription
    {
        return $this->subscribe(
            $subscription->farm,
            $subscription->plan,
            $subscription->billing_cycle,
            $createdBy
        );
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(FarmSubscription $subscription, ?string $reason = null): void
    {
        $subscription->cancel($reason);
    }

    /**
     * Get subscription statistics.
     */
    public function getStats(): array
    {
        return [
            'total_active' => FarmSubscription::active()->count(),
            'total_pending' => FarmSubscription::pending()->count(),
            'pending_invoices' => SubscriptionInvoice::pending()->count(),
            'overdue_invoices' => SubscriptionInvoice::overdue()->count(),
            'revenue_this_month' => SubscriptionInvoice::paid()
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
        ];
    }
}
