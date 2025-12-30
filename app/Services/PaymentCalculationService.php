<?php

namespace App\Services;

use App\Models\MilkPayment;
use App\Models\MilkBatch;
use App\Models\Farm;
use App\Notifications\FarmProductionNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentCalculationService
{
    /**
     * Calculate payment for a farm for a given period.
     */
    public function calculatePayment(string $farmId, string $periodStart, string $periodEnd): array
    {
        $farm = Farm::findOrFail($farmId);

        // Get all approved batches in this period
        $batches = MilkBatch::where('farm_id', $farmId)
            ->where('status', 'approved')
            ->whereBetween('collection_date', [$periodStart, $periodEnd])
            ->get();

        if ($batches->isEmpty()) {
            throw new \Exception('Tidak ada batch susu yang disetujui dalam periode ini.');
        }

        // Get pricing from farm settings or use defaults
        $pricing = $this->getPricing($farm);

        // Group batches by grade and calculate
        $gradeBreakdown = [];
        $totalLiters = 0;
        $grossAmount = 0;

        foreach (['A', 'B', 'C'] as $grade) {
            $gradeBatches = $batches->where('quality_grade', $grade);
            $liters = $gradeBatches->sum('total_volume');
            $rate = $pricing["grade_" . strtolower($grade)] ?? 0;
            $amount = $liters * $rate;

            if ($liters > 0) {
                $gradeBreakdown[$grade] = [
                    'liters' => $liters,
                    'rate' => $rate,
                    'amount' => $amount,
                ];

                $totalLiters += $liters;
                $grossAmount += $amount;
            }
        }

        return [
            'farm_id' => $farmId,
            'farm_name' => $farm->name,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_batches' => $batches->count(),
            'total_liters' => $totalLiters,
            'grade_breakdown' => $gradeBreakdown,
            'gross_amount' => $grossAmount,
            'pricing' => $pricing,
        ];
    }

    /**
     * Create a payment record.
     */
    public function createPayment(
        string $farmId,
        string $periodStart,
        string $periodEnd,
        array $deductions = [],
        ?string $notes = null
    ): MilkPayment {
        return DB::transaction(function () use ($farmId, $periodStart, $periodEnd, $deductions, $notes) {
            // Calculate payment
            $calculation = $this->calculatePayment($farmId, $periodStart, $periodEnd);

            // Calculate deductions
            $deductionsTotal = array_reduce($deductions, function ($sum, $deduction) {
                return $sum + ($deduction['amount'] ?? 0);
            }, 0);

            $netAmount = $calculation['gross_amount'] - $deductionsTotal;

            // Create payment record
            $payment = MilkPayment::create([
                'farm_id' => $farmId,
                'payment_period_start' => $periodStart,
                'payment_period_end' => $periodEnd,
                'total_liters' => $calculation['total_liters'],
                'grade_breakdown' => $calculation['grade_breakdown'],
                'gross_amount' => $calculation['gross_amount'],
                'deductions' => $deductions,
                'deductions_total' => $deductionsTotal,
                'net_amount' => $netAmount,
                'status' => 'draft',
                'calculated_by_user_id' => Auth::id(),
                'calculated_at' => now(),
                'notes' => $notes,
            ]);

            // Send notification to farm owner
            $this->sendPaymentNotification($payment);

            return $payment;
        });
    }

    /**
     * Get pricing for a farm.
     */
    private function getPricing(Farm $farm): array
    {
        // If farm has custom pricing, use it
        if ($farm->milk_pricing && is_array($farm->milk_pricing)) {
            return $farm->milk_pricing;
        }

        // Otherwise use default pricing (IDR per liter)
        return [
            'grade_a' => 12000,
            'grade_b' => 10000,
            'grade_c' => 8000,
        ];
    }

    /**
     * Calculate payment summary for multiple farms.
     */
    public function calculateBulkPayments(array $farmIds, string $periodStart, string $periodEnd): array
    {
        $summary = [];

        foreach ($farmIds as $farmId) {
            try {
                $calculation = $this->calculatePayment($farmId, $periodStart, $periodEnd);
                $summary[] = $calculation;
            } catch (\Exception $e) {
                // Skip farms with no approved batches
                continue;
            }
        }

        return $summary;
    }

    /**
     * Get payment history summary for a farm.
     */
    public function getPaymentHistory(string $farmId, int $months = 6): array
    {
        $payments = MilkPayment::where('farm_id', $farmId)
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths($months))
            ->orderBy('paid_at', 'desc')
            ->get();

        $monthlyData = [];
        foreach ($payments as $payment) {
            $month = Carbon::parse($payment->paid_at)->format('Y-m');
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'month' => $month,
                    'count' => 0,
                    'total_amount' => 0,
                    'total_liters' => 0,
                ];
            }

            $monthlyData[$month]['count']++;
            $monthlyData[$month]['total_amount'] += $payment->net_amount;
            $monthlyData[$month]['total_liters'] += $payment->total_liters;
        }

        return [
            'total_paid' => $payments->sum('net_amount'),
            'total_liters' => $payments->sum('total_liters'),
            'payment_count' => $payments->count(),
            'monthly_data' => array_values($monthlyData),
        ];
    }

    /**
     * Send notification about new payment.
     */
    private function sendPaymentNotification(MilkPayment $payment): void
    {
        try {
            $farm = Farm::find($payment->farm_id);
            if ($farm && $farm->owner) {
                $farm->owner->notify(
                    FarmProductionNotification::paymentCreated(
                        $payment->payment_period_start,
                        $payment->payment_period_end,
                        $payment->net_amount,
                        $farm->name
                    )
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send payment notification: ' . $e->getMessage());
        }
    }
}
