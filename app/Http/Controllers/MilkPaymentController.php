<?php

namespace App\Http\Controllers;

use App\Models\MilkPayment;
use App\Models\MilkBatch;
use App\Models\Farm;
use App\Services\PaymentCalculationService;
use App\Traits\HasCurrentFarm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MilkPaymentController extends Controller
{
    use HasCurrentFarm;

    protected $paymentService;

    public function __construct(PaymentCalculationService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Finance view - all payments for management.
     */
    public function financeIndex(Request $request)
    {
        $query = MilkPayment::with(['farm:id,name', 'calculatedBy:id,name', 'approvedBy:id,name', 'paidBy:id,name']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by farm
        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->get('farm_id'));
        }

        // Filter by period
        if ($request->filled('period_from')) {
            $query->where('payment_period_start', '>=', $request->get('period_from'));
        }
        if ($request->filled('period_to')) {
            $query->where('payment_period_end', '<=', $request->get('period_to'));
        }

        $payments = $query->orderBy('payment_period_start', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get all farms for filter
        $farms = Farm::where('farm_type', 'milk_supplier')
            ->orWhere('farm_type', 'both')
            ->get(['id', 'name']);

        // Calculate stats
        $stats = [
            'draft_count' => MilkPayment::where('status', 'draft')->count(),
            'draft_amount' => MilkPayment::where('status', 'draft')->sum('net_amount'),
            'approved_count' => MilkPayment::where('status', 'approved')->count(),
            'approved_amount' => MilkPayment::where('status', 'approved')->sum('net_amount'),
            'paid_count_month' => MilkPayment::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->count(),
            'paid_amount_month' => MilkPayment::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('net_amount'),
            'paid_amount_year' => MilkPayment::where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->sum('net_amount'),
        ];

        return Inertia::render('Payments/FinanceView', [
            'payments' => $payments,
            'farms' => $farms,
            'filters' => [
                'status' => $request->get('status'),
                'farm_id' => $request->get('farm_id'),
                'period_from' => $request->get('period_from'),
                'period_to' => $request->get('period_to'),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Farmer view - their payment history.
     */
    public function farmerIndex(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return Inertia::render('Payments/FarmerView', [
                'payments' => ['data' => [], 'total' => 0],
                'stats' => null,
            ]);
        }

        $query = MilkPayment::with(['calculatedBy:id,name', 'approvedBy:id,name', 'paidBy:id,name'])
            ->where('farm_id', $currentFarmId);

        $payments = $query->orderBy('payment_period_start', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calculate farmer stats
        $stats = [
            'pending_amount' => MilkPayment::where('farm_id', $currentFarmId)
                ->whereIn('status', ['draft', 'approved'])
                ->sum('net_amount'),
            'pending_count' => MilkPayment::where('farm_id', $currentFarmId)
                ->whereIn('status', ['draft', 'approved'])
                ->count(),
            'paid_month' => MilkPayment::where('farm_id', $currentFarmId)
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('net_amount'),
            'paid_count_month' => MilkPayment::where('farm_id', $currentFarmId)
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->count(),
            'paid_year' => MilkPayment::where('farm_id', $currentFarmId)
                ->where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->sum('net_amount'),
            'total_liters_year' => MilkBatch::where('farm_id', $currentFarmId)
                ->where('status', 'approved')
                ->whereYear('collection_date', now()->year)
                ->sum('total_volume'),
            'avg_monthly' => MilkPayment::where('farm_id', $currentFarmId)
                ->where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->avg('net_amount'),
        ];

        return Inertia::render('Payments/FarmerView', [
            'payments' => $payments,
            'stats' => $stats,
        ]);
    }

    /**
     * Show calculate payment form.
     */
    public function calculateForm(Request $request)
    {
        $farmId = $request->get('farm_id');
        $periodStart = $request->get('payment_period_start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $periodEnd = $request->get('payment_period_end', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get farms that are milk suppliers
        $farms = Farm::where('farm_type', 'milk_supplier')
            ->orWhere('farm_type', 'both')
            ->get(['id', 'name']);

        $preview = null;
        if ($farmId && $request->filled('preview')) {
            // Calculate payment preview
            $preview = $this->paymentService->calculatePayment($farmId, $periodStart, $periodEnd);
        }

        return Inertia::render('Payments/CalculateForm', [
            'farms' => $farms,
            'preview' => $preview,
        ]);
    }

    /**
     * Store calculated payment.
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'deductions' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->paymentService->createPayment(
                $validated['farm_id'],
                $validated['period_start'],
                $validated['period_end'],
                $validated['deductions'] ?? [],
                $validated['notes'] ?? null
            );

            return redirect()->route('payments.finance')
                ->with('success', 'Pembayaran berhasil dihitung dan disimpan sebagai draft.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Approve a payment.
     */
    public function approve(string $id)
    {
        $payment = MilkPayment::findOrFail($id);

        if ($payment->status !== 'draft') {
            return redirect()->back()
                ->withErrors(['error' => 'Payment can only be approved from draft status.']);
        }

        $payment->update([
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Pembayaran telah disetujui.');
    }

    /**
     * Mark payment as paid.
     */
    public function markAsPaid(Request $request, string $id)
    {
        $payment = MilkPayment::findOrFail($id);

        if ($payment->status !== 'approved') {
            return redirect()->back()
                ->withErrors(['error' => 'Payment must be approved before marking as paid.']);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'payment_proof_path' => 'nullable|string',
        ]);

        $payment->update([
            'status' => 'paid',
            'paid_by_user_id' => auth()->id(),
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_proof_path' => $validated['payment_proof_path'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Pembayaran telah ditandai sebagai lunas.');
    }

    /**
     * Show payment details.
     */
    public function show(string $id)
    {
        $payment = MilkPayment::with([
            'farm:id,name',
            'calculatedBy:id,name',
            'approvedBy:id,name',
            'paidBy:id,name'
        ])->findOrFail($id);

        // Get related milk batches
        $batches = MilkBatch::where('farm_id', $payment->farm_id)
            ->where('status', 'approved')
            ->whereBetween('collection_date', [$payment->payment_period_start, $payment->payment_period_end])
            ->get();

        return Inertia::render('Payments/Show', [
            'payment' => $payment,
            'relatedBatches' => $batches,
        ]);
    }

    /**
     * Calculate average grade for a farm.
     */
    private function calculateAverageGrade(string $farmId)
    {
        $grades = MilkBatch::where('farm_id', $farmId)
            ->where('status', 'approved')
            ->whereNotNull('quality_grade')
            ->pluck('quality_grade');

        if ($grades->isEmpty()) {
            return null;
        }

        $gradeValues = $grades->map(function ($grade) {
            return match ($grade) {
                'A' => 4,
                'B' => 3,
                'C' => 2,
                default => 1,
            };
        });

        $avg = $gradeValues->average();

        if ($avg >= 3.5) return 'A';
        if ($avg >= 2.5) return 'B';
        if ($avg >= 1.5) return 'C';
        return 'D';
    }
}
