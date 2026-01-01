<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\FarmSubscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminBillingController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {
        $this->middleware('super.user');
    }

    /**
     * Billing dashboard with overview stats.
     */
    public function index()
    {
        $stats = $this->subscriptionService->getStats();

        // Get recent subscriptions
        $recentSubscriptions = FarmSubscription::with(['farm', 'plan'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($sub) => [
                'id' => $sub->id,
                'farm_name' => $sub->farm->name,
                'plan_name' => $sub->plan->name,
                'billing_cycle' => $sub->billing_cycle,
                'status' => $sub->status,
                'price' => $sub->price,
                'starts_at' => $sub->starts_at?->toISOString(),
                'ends_at' => $sub->ends_at?->toISOString(),
                'created_at' => $sub->created_at->toISOString(),
            ]);

        // Get pending invoices
        $pendingInvoices = SubscriptionInvoice::with(['farm', 'plan'])
            ->pending()
            ->orderBy('due_date')
            ->take(10)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'farm_name' => $inv->farm->name,
                'plan_name' => $inv->plan->name,
                'total' => $inv->total,
                'due_date' => $inv->due_date->toISOString(),
                'is_overdue' => $inv->isOverdue(),
            ]);

        // Subscription by plan stats
        $planStats = SubscriptionPlan::withCount(['subscriptions' => function ($query) {
            $query->where('status', 'active');
        }])->orderBy('sort_order')->get()->map(fn($plan) => [
            'name' => $plan->name,
            'active_count' => $plan->subscriptions_count,
            'monthly_price' => $plan->monthly_price,
            'annual_price' => $plan->annual_price,
        ]);

        return Inertia::render('AdminBilling/Index', [
            'stats' => $stats,
            'recentSubscriptions' => $recentSubscriptions,
            'pendingInvoices' => $pendingInvoices,
            'planStats' => $planStats,
        ]);
    }

    /**
     * List all subscription plans.
     */
    public function plans()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')
            ->get()
            ->map(fn($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'monthly_price' => $plan->monthly_price,
                'annual_price' => $plan->annual_price,
                'max_livestock' => $plan->max_livestock,
                'max_users' => $plan->max_users,
                'features' => $plan->features,
                'has_analytics' => $plan->has_analytics,
                'has_iot' => $plan->has_iot,
                'has_expert_support' => $plan->has_expert_support,
                'is_active' => $plan->is_active,
                'is_visible' => $plan->is_visible,
                'sort_order' => $plan->sort_order,
                'subscriptions_count' => $plan->subscriptions()->active()->count(),
            ]);

        return Inertia::render('AdminBilling/Plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store a new plan.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans,slug',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
            'max_livestock' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'has_analytics' => 'boolean',
            'has_iot' => 'boolean',
            'has_expert_support' => 'boolean',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.billing.plans')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    /**
     * Update a plan.
     */
    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
            'max_livestock' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'has_analytics' => 'boolean',
            'has_iot' => 'boolean',
            'has_expert_support' => 'boolean',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $plan->update($validated);

        return redirect()->route('admin.billing.plans')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    /**
     * Delete a plan.
     */
    public function destroyPlan(SubscriptionPlan $plan)
    {
        // Check if plan has active subscriptions
        if ($plan->subscriptions()->active()->exists()) {
            return back()->withErrors([
                'plan' => 'Paket tidak dapat dihapus karena masih memiliki langganan aktif.'
            ]);
        }

        $plan->delete();

        return redirect()->route('admin.billing.plans')
            ->with('success', 'Paket berhasil dihapus.');
    }

    /**
     * Toggle plan visibility.
     */
    public function togglePlanVisibility(SubscriptionPlan $plan)
    {
        $plan->update(['is_visible' => !$plan->is_visible]);

        $message = $plan->is_visible
            ? 'Paket berhasil ditampilkan.'
            : 'Paket berhasil disembunyikan.';

        return back()->with('success', $message);
    }

    /**
     * List all subscriptions.
     */
    public function subscriptions(Request $request)
    {
        $query = FarmSubscription::with(['farm', 'plan', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $subscriptions = $query->latest()
            ->paginate(20)
            ->through(fn($sub) => [
                'id' => $sub->id,
                'farm_name' => $sub->farm->name,
                'plan_name' => $sub->plan->name,
                'billing_cycle' => $sub->billing_cycle,
                'price' => $sub->price,
                'status' => $sub->status,
                'starts_at' => $sub->starts_at?->toISOString(),
                'ends_at' => $sub->ends_at?->toISOString(),
                'days_remaining' => $sub->daysRemaining(),
                'auto_renew' => $sub->auto_renew,
                'created_by' => $sub->createdBy?->name,
                'created_at' => $sub->created_at->toISOString(),
            ]);

        $plans = SubscriptionPlan::orderBy('sort_order')->get(['id', 'name']);

        return Inertia::render('AdminBilling/Subscriptions', [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
            'filters' => $request->only(['status', 'plan_id']),
        ]);
    }

    /**
     * List all invoices.
     */
    public function invoices(Request $request)
    {
        $query = SubscriptionInvoice::with(['farm', 'plan', 'paidBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()
            ->paginate(20)
            ->through(fn($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'farm_name' => $inv->farm->name,
                'plan_name' => $inv->plan->name,
                'subtotal' => $inv->subtotal,
                'discount' => $inv->discount,
                'total' => $inv->total,
                'status' => $inv->status,
                'due_date' => $inv->due_date->toISOString(),
                'paid_at' => $inv->paid_at?->toISOString(),
                'payment_method' => $inv->payment_method,
                'is_overdue' => $inv->isOverdue(),
                'paid_by' => $inv->paidBy?->name,
                'created_at' => $inv->created_at->toISOString(),
            ]);

        return Inertia::render('AdminBilling/Invoices', [
            'invoices' => $invoices,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Manually mark invoice as paid (admin override).
     */
    public function markInvoicePaid(Request $request, SubscriptionInvoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->subscriptionService->processPayment(
            $invoice,
            $request->payment_method,
            $request->payment_reference,
            null,
            Auth::id()
        );

        if ($request->notes) {
            $invoice->update(['notes' => $request->notes]);
        }

        return back()->with('success', 'Invoice berhasil ditandai lunas.');
    }

    /**
     * Cancel an invoice.
     */
    public function cancelInvoice(SubscriptionInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->withErrors(['invoice' => 'Invoice yang sudah lunas tidak dapat dibatalkan.']);
        }

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Invoice berhasil dibatalkan.');
    }
}
