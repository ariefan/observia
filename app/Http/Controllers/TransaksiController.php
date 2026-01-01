<?php

namespace App\Http\Controllers;

use App\Models\FarmSubscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransaksiController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    public function paketLayanan()
    {
        $user = Auth::user();
        $farm = $user->currentFarm;

        // Get all available plans
        $plans = $this->subscriptionService->getAvailablePlans();

        // Get current subscription for the farm
        $currentSubscription = $farm?->activeSubscription?->load('plan');
        $currentPlanSlug = $currentSubscription?->plan?->slug;

        // Format plans for frontend
        $packages = $plans->map(function ($plan) use ($currentPlanSlug, $farm) {
            $isCurrent = $currentPlanSlug === $plan->slug;
            $canUpgrade = $farm && $this->subscriptionService->canUpgrade($farm, $plan);

            // Determine button text and variant
            $buttonText = 'Pilih Paket';
            $buttonVariant = 'outline';

            if ($isCurrent) {
                $buttonText = 'Paket Saat Ini';
                $buttonVariant = 'secondary';
            } elseif ($plan->isFree()) {
                $buttonText = 'Mulai Gratis';
            } elseif ($canUpgrade) {
                $buttonText = 'Upgrade';
                $buttonVariant = 'default';
            } else {
                $buttonText = 'Downgrade';
            }

            return [
                'id' => $plan->id,
                'slug' => $plan->slug,
                'name' => $plan->name,
                'description' => $plan->description,
                'monthly_price' => $plan->monthly_price,
                'annual_price' => $plan->annual_price,
                'annual_savings' => $plan->getAnnualSavingsPercentage(),
                'features' => $plan->features,
                'max_livestock' => $plan->max_livestock,
                'max_users' => $plan->max_users,
                'has_analytics' => $plan->has_analytics,
                'has_iot' => $plan->has_iot,
                'has_expert_support' => $plan->has_expert_support,
                'is_free' => $plan->isFree(),
                'is_current' => $isCurrent,
                'can_select' => !$isCurrent,
                'button_text' => $buttonText,
                'button_variant' => $buttonVariant,
            ];
        });

        // Get pending invoices count for badge
        $pendingInvoices = $farm
            ? $farm->subscriptionInvoices()->pending()->count()
            : 0;

        return Inertia::render('Transaksi/PaketLayanan', [
            'packages' => $packages,
            'currentSubscription' => $currentSubscription ? [
                'id' => $currentSubscription->id,
                'plan_name' => $currentSubscription->plan->name,
                'billing_cycle' => $currentSubscription->billing_cycle,
                'price' => $currentSubscription->price,
                'status' => $currentSubscription->status,
                'starts_at' => $currentSubscription->starts_at?->toISOString(),
                'ends_at' => $currentSubscription->ends_at?->toISOString(),
                'days_remaining' => $currentSubscription->daysRemaining(),
                'auto_renew' => $currentSubscription->auto_renew,
            ] : null,
            'pendingInvoices' => $pendingInvoices,
        ]);
    }

    public function tagihan()
    {
        $user = Auth::user();
        $farm = $user->currentFarm;

        $invoices = $farm
            ? $this->subscriptionService->getInvoices($farm)
            : collect();

        $formattedInvoices = $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'plan_name' => $invoice->plan->name,
                'billing_cycle' => $invoice->subscription->billing_cycle,
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount,
                'tax' => $invoice->tax,
                'total' => $invoice->total,
                'status' => $invoice->status,
                'due_date' => $invoice->due_date->toISOString(),
                'paid_at' => $invoice->paid_at?->toISOString(),
                'is_overdue' => $invoice->isOverdue(),
                'created_at' => $invoice->created_at->toISOString(),
            ];
        });

        return Inertia::render('Transaksi/Tagihan', [
            'invoices' => $formattedInvoices,
        ]);
    }

    public function riwayatPembayaran()
    {
        $user = Auth::user();
        $farm = $user->currentFarm;

        $payments = $farm
            ? $this->subscriptionService->getPaymentHistory($farm)
            : collect();

        $formattedPayments = $payments->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'plan_name' => $invoice->plan->name,
                'billing_cycle' => $invoice->subscription->billing_cycle,
                'total' => $invoice->total,
                'payment_method' => $invoice->payment_method,
                'payment_reference' => $invoice->payment_reference,
                'paid_at' => $invoice->paid_at->toISOString(),
                'paid_by' => $invoice->paidBy?->name,
            ];
        });

        return Inertia::render('Transaksi/RiwayatPembayaran', [
            'payments' => $formattedPayments,
        ]);
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $user = Auth::user();
        $farm = $user->currentFarm;

        if (!$farm) {
            return back()->withErrors(['farm' => 'Anda belum memiliki peternakan.']);
        }

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        $subscription = $this->subscriptionService->subscribe(
            $farm,
            $plan,
            $request->billing_cycle,
            $user->id
        );

        if ($plan->isFree()) {
            return redirect()->route('transaksi.paket-layanan')
                ->with('success', 'Berhasil berlangganan paket ' . $plan->name);
        }

        // Redirect to invoice for paid plans
        $invoice = $subscription->invoices()->latest()->first();

        return redirect()->route('transaksi.tagihan')
            ->with('success', 'Tagihan telah dibuat. Silakan lakukan pembayaran.');
    }

    /**
     * Pay an invoice.
     */
    public function payInvoice(Request $request, SubscriptionInvoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = Auth::user();
        $farm = $user->currentFarm;

        // Ensure user owns this invoice
        if ($invoice->farm_id !== $farm->id) {
            abort(403);
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
        }

        $this->subscriptionService->processPayment(
            $invoice,
            $request->payment_method,
            $request->payment_reference,
            $proofPath,
            $user->id
        );

        return redirect()->route('transaksi.riwayat-pembayaran')
            ->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $farm = $user->currentFarm;

        $subscription = $farm->activeSubscription;

        if (!$subscription) {
            return back()->withErrors(['subscription' => 'Tidak ada langganan aktif.']);
        }

        $this->subscriptionService->cancelSubscription($subscription, $request->reason);

        return redirect()->route('transaksi.paket-layanan')
            ->with('success', 'Langganan berhasil dibatalkan.');
    }

    /**
     * Toggle auto-renew.
     */
    public function toggleAutoRenew()
    {
        $user = Auth::user();
        $farm = $user->currentFarm;

        $subscription = $farm->activeSubscription;

        if (!$subscription) {
            return back()->withErrors(['subscription' => 'Tidak ada langganan aktif.']);
        }

        $subscription->update([
            'auto_renew' => !$subscription->auto_renew,
        ]);

        $message = $subscription->auto_renew
            ? 'Perpanjangan otomatis diaktifkan.'
            : 'Perpanjangan otomatis dinonaktifkan.';

        return back()->with('success', $message);
    }
}
