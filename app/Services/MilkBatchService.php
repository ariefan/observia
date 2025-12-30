<?php

namespace App\Services;

use App\Models\MilkBatch;
use App\Models\LivestockMilking;
use App\Models\Setting;
use App\Models\Farm;
use App\Notifications\FarmProductionNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MilkBatchService
{
    /**
     * Create a new milk batch from livestock milking records.
     */
    public function createBatch(string $farmId, array $data): MilkBatch
    {
        return DB::transaction(function () use ($farmId, $data) {
            // Generate batch code
            $batchCode = $this->generateBatchCode($data['collection_date']);

            // Calculate variance percentage
            $variancePercentage = $this->calculateVariance(
                $data['estimated_volume'],
                $data['actual_volume']
            );

            // Create the batch
            $batch = MilkBatch::create([
                'batch_code' => $batchCode,
                'farm_id' => $farmId,
                'collection_date' => $data['collection_date'],
                'session' => $data['session'],
                'total_volume' => $data['actual_volume'],
                'source_livestock_milking_ids' => $data['source_livestock_milking_ids'],
                'collected_by_user_id' => Auth::id(),
                'collected_at' => now(),
                'estimated_volume' => $data['estimated_volume'],
                'actual_volume' => $data['actual_volume'],
                'variance_percentage' => $variancePercentage,
                'transport_temp_pickup' => $data['transport_temp_pickup'],
                'transport_notes' => $data['transport_notes'] ?? null,
                'status' => 'collected',
            ]);

            // Send notification to farm owner about new collection
            $this->sendCollectionNotification($batch);

            return $batch;
        });
    }

    /**
     * Update batch with receiving information.
     */
    public function updateReceiving(MilkBatch $batch, array $data): MilkBatch
    {
        if (!in_array($batch->status, ['collected', 'in_transit'])) {
            throw new \Exception('Batch cannot be received in current status: ' . $batch->status);
        }

        // Check temperature thresholds
        $tempThreshold = $this->getTemperatureThreshold();
        if ($data['transport_temp_delivery'] > $tempThreshold['warning_threshold']) {
            // Temperature breach warning
            $data['transport_notes'] = ($data['transport_notes'] ?? '') .
                " [WARNING: Suhu pengiriman melebihi ambang batas: {$data['transport_temp_delivery']}°C]";
        }

        // Auto-reject if visual or smell check is abnormal
        $status = 'received';
        $rejectionReason = null;

        if (in_array($data['visual_check'], ['abnormal', 'foamy', 'discolored']) ||
            in_array($data['smell_check'], ['sour', 'abnormal'])) {
            $status = 'rejected';
            $rejectionReason = 'Gagal pemeriksaan visual/bau: ' .
                "Visual: {$data['visual_check']}, Bau: {$data['smell_check']}";
        }

        $batch->update([
            'received_by_user_id' => Auth::id(),
            'received_at' => now(),
            'transport_temp_delivery' => $data['transport_temp_delivery'],
            'transport_duration_minutes' => $data['transport_duration_minutes'] ?? null,
            'visual_check' => $data['visual_check'],
            'smell_check' => $data['smell_check'],
            'transport_notes' => $data['transport_notes'] ?? $batch->transport_notes,
            'status' => $status,
            'rejection_reason' => $rejectionReason,
        ]);

        // Send notification about batch status
        $this->sendStatusNotification($batch);

        return $batch->fresh();
    }

    /**
     * Update batch with quality test results and calculate grade.
     */
    public function updateQualityTest(MilkBatch $batch, array $data): MilkBatch
    {
        if ($batch->status !== 'received') {
            throw new \Exception('Batch must be received before quality testing. Current status: ' . $batch->status);
        }

        // Calculate grade based on quality standards
        $grade = $this->calculateGrade($data['quality_data']);

        // Determine final status
        $status = $grade === 'Reject' ? 'rejected' : 'approved';
        $rejectionReason = $grade === 'Reject' ? 'Gagal uji kualitas laboratorium' : null;

        $batch->update([
            'quality_tested_by_user_id' => Auth::id(),
            'quality_tested_at' => now(),
            'quality_data' => $data['quality_data'],
            'quality_grade' => $grade,
            'quality_notes' => $data['quality_notes'] ?? null,
            'status' => $status,
            'rejection_reason' => $batch->rejection_reason ?? $rejectionReason,
        ]);

        // Send notification about quality test results
        $this->sendQualityTestNotification($batch);

        return $batch->fresh();
    }

    /**
     * Generate unique batch code.
     */
    private function generateBatchCode(string $collectionDate): string
    {
        $date = Carbon::parse($collectionDate);
        $dateStr = $date->format('Ymd');

        // Get last batch code for this date
        $lastBatch = MilkBatch::where('batch_code', 'LIKE', "MB-{$dateStr}-%")
            ->orderBy('batch_code', 'desc')
            ->first();

        $sequence = 1;
        if ($lastBatch) {
            // Extract sequence number from last batch code
            $parts = explode('-', $lastBatch->batch_code);
            $sequence = (int)end($parts) + 1;
        }

        return sprintf('MB-%s-%03d', $dateStr, $sequence);
    }

    /**
     * Calculate variance percentage between estimated and actual volume.
     */
    private function calculateVariance(float $estimated, float $actual): float
    {
        if ($estimated == 0) {
            return 0;
        }

        return round((($actual - $estimated) / $estimated) * 100, 2);
    }

    /**
     * Calculate milk grade based on quality standards.
     */
    private function calculateGrade(array $qualityData): string
    {
        $standards = $this->getQualityStandards();

        // Check against Grade A
        if ($this->meetsGradeStandard($qualityData, $standards['grade_a'])) {
            return 'A';
        }

        // Check against Grade B
        if ($this->meetsGradeStandard($qualityData, $standards['grade_b'])) {
            return 'B';
        }

        // Check against Grade C
        if ($this->meetsGradeStandard($qualityData, $standards['grade_c'])) {
            return 'C';
        }

        // If doesn't meet any grade, reject
        return 'Reject';
    }

    /**
     * Check if quality data meets grade standard.
     */
    private function meetsGradeStandard(array $qualityData, array $standard): bool
    {
        // Check pH range
        if ($qualityData['pH'] < $standard['pH_min'] || $qualityData['pH'] > $standard['pH_max']) {
            return false;
        }

        // Check fat percentage
        if ($qualityData['fat_percentage'] < $standard['fat_min']) {
            return false;
        }

        // Check bacteria count
        if ($qualityData['bacteria_count'] > $standard['bacteria_max']) {
            return false;
        }

        return true;
    }

    /**
     * Get milk quality standards from settings.
     */
    private function getQualityStandards(): array
    {
        $setting = Setting::where('key', 'milk_quality_standards')->first();

        if (!$setting) {
            // Default standards if not found
            return [
                'grade_a' => [
                    'pH_min' => 6.6,
                    'pH_max' => 6.8,
                    'fat_min' => 3.5,
                    'bacteria_max' => 100000,
                ],
                'grade_b' => [
                    'pH_min' => 6.5,
                    'pH_max' => 6.9,
                    'fat_min' => 3.0,
                    'bacteria_max' => 500000,
                ],
                'grade_c' => [
                    'pH_min' => 6.4,
                    'pH_max' => 7.0,
                    'fat_min' => 2.5,
                    'bacteria_max' => 1000000,
                ],
            ];
        }

        return json_decode($setting->value, true);
    }

    /**
     * Get temperature range settings.
     */
    private function getTemperatureThreshold(): array
    {
        $setting = Setting::where('key', 'milk_temperature_range')->first();

        if (!$setting) {
            // Default thresholds
            return [
                'pickup_min' => 4,
                'pickup_max' => 7,
                'warning_threshold' => 10,
            ];
        }

        return json_decode($setting->value, true);
    }

    /**
     * Aggregate livestock milkings into a summary.
     */
    public function aggregateMilkings(array $milkingIds): array
    {
        $milkings = LivestockMilking::whereIn('id', $milkingIds)->get();

        return [
            'total_volume' => $milkings->sum('milk_volume'),
            'count' => $milkings->count(),
            'unique_livestock' => $milkings->pluck('livestock_id')->unique()->count(),
        ];
    }

    /**
     * Send notification about new milk collection.
     */
    private function sendCollectionNotification(MilkBatch $batch): void
    {
        try {
            $farm = Farm::find($batch->farm_id);
            if ($farm && $farm->owner) {
                $farm->owner->notify(
                    FarmProductionNotification::milkCollection(
                        $batch->batch_code,
                        $batch->total_volume,
                        $farm->name
                    )
                );
            }
        } catch (\Exception $e) {
            // Log but don't fail the main operation
            \Log::warning('Failed to send milk collection notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification about batch status change.
     */
    private function sendStatusNotification(MilkBatch $batch): void
    {
        try {
            $farm = Farm::find($batch->farm_id);
            if ($farm && $farm->owner) {
                $farm->owner->notify(
                    FarmProductionNotification::milkBatchStatus(
                        $batch->batch_code,
                        $batch->status,
                        $farm->name,
                        $batch->rejection_reason
                    )
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send milk batch status notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification about quality test results.
     */
    private function sendQualityTestNotification(MilkBatch $batch): void
    {
        try {
            $farm = Farm::find($batch->farm_id);
            if ($farm && $farm->owner) {
                $farm->owner->notify(
                    FarmProductionNotification::qualityTest(
                        $batch->batch_code,
                        $batch->quality_grade,
                        $farm->name
                    )
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send quality test notification: ' . $e->getMessage());
        }
    }
}
