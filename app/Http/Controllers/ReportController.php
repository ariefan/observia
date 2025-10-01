<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Livestock;
use App\Models\HerdFeeding;
use App\Models\LivestockMilking;
use App\Models\LivestockWeight;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->current_farm_id;

        if (!$farmId) {
            return response()->json(['error' => 'No farm selected'], 400);
        }

        $reports = Report::forUser($user->id)
            ->forFarm($farmId)
            ->recent(30)
            ->completed()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'name' => $report->display_name . ' - ' . $report->start_date->format('M Y'),
                    'format' => $report->format,
                    'created_at' => $report->created_at->toISOString(),
                    'file_size' => $report->file_size_formatted,
                    'download_count' => $report->download_count,
                    'download_url' => $report->getDownloadUrl(),
                ];
            });

        $stats = [
            'total_reports' => Report::forUser($user->id)->forFarm($farmId)->count(),
            'monthly_reports' => Report::forUser($user->id)->forFarm($farmId)
                ->where('created_at', '>=', now()->startOfMonth())->count(),
            'total_downloads' => Report::forUser($user->id)->forFarm($farmId)->sum('download_count'),
        ];

        return response()->json([
            'reports' => $reports,
            'stats' => $stats,
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:livestock-summary,feeding-report,milking-report,weight-report,health-report,productivity-report,financial-report,breeding-report',
            'format' => 'required|string|in:pdf,excel,csv',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'livestock_id' => 'nullable|exists:livestocks,id',
        ]);

        $user = $request->user();
        $farmId = $user->current_farm_id;

        if (!$farmId) {
            return response()->json(['error' => 'No farm selected'], 400);
        }

        // Create report record
        $report = Report::create([
            'user_id' => $user->id,
            'farm_id' => $farmId,
            'type' => $request->type,
            'name' => $this->generateReportName($request->type, $request->start_date, $request->end_date),
            'format' => $request->format,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'filters' => $request->only(['livestock_id']),
            'status' => 'generating',
        ]);

        try {
            // Generate the report file
            $filePath = $this->generateReportFile($report, $request->all());
            
            $report->update([
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'status' => 'completed',
            ]);

            // Always return JSON for report generation since we need to handle downloads
            // Inertia will treat this as a regular AJAX call

            return response()->json([
                'message' => 'Report generated successfully',
                'report' => [
                    'id' => $report->id,
                    'name' => $report->display_name,
                    'download_url' => $report->getDownloadUrl(),
                ],
            ]);

        } catch (\Exception $e) {
            $report->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Return JSON error for consistency

            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    public function download(Report $report): Response
    {
        $user = request()->user();

        // Check permissions
        if ($report->user_id !== $user->id || $report->farm_id !== $user->current_farm_id) {
            abort(404);
        }

        if (!$report->file_path || !Storage::exists($report->file_path)) {
            abort(404, 'Report file not found');
        }

        $report->incrementDownloadCount();

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
        ];

        return response(Storage::get($report->file_path))
            ->header('Content-Type', $mimeTypes[$report->format])
            ->header('Content-Disposition', 'attachment; filename="' . $report->name . '.' . $this->getFileExtension($report->format) . '"');
    }

    public function destroy(Report $report): JsonResponse
    {
        $user = request()->user();

        // Check permissions
        if ($report->user_id !== $user->id || $report->farm_id !== $user->current_farm_id) {
            abort(404);
        }

        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }

    private function generateReportFile(Report $report, array $params): string
    {
        $data = $this->collectReportData($report->type, $params, $report->user, $report->farm_id);
        
        switch ($report->format) {
            case 'pdf':
                return $this->generatePdfReport($report, $data);
            case 'excel':
                return $this->generateExcelReport($report, $data);
            case 'csv':
                return $this->generateCsvReport($report, $data);
            default:
                throw new \Exception('Unsupported format: ' . $report->format);
        }
    }

    private function collectReportData(string $type, array $params, $user = null, $farmId = null): array
    {
        if (!$user) {
            $user = request()->user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }
        }
        if (!$farmId) {
            $farmId = $user->current_farm_id;
        }
        $startDate = $params['start_date'];
        $endDate = $params['end_date'];
        $livestockId = $params['livestock_id'] ?? null;

        $livestockQuery = function () use ($farmId) {
            return Livestock::where('farm_id', $farmId);
        };
        
        $herdQuery = function () use ($farmId) {
            return HerdFeeding::whereHas('herd', function ($query) use ($farmId) {
                $query->where('farm_id', $farmId);
            });
        };

        switch ($type) {
            case 'livestock-summary':
                $livestocks = Livestock::where('farm_id', $farmId)
                    ->with(['breed.species'])
                    ->when($livestockId, function ($query) use ($livestockId) {
                        return $query->where('id', $livestockId);
                    })
                    ->get();

                return [
                    'title' => 'Ringkasan Ternak',
                    'period' => \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y'),
                    'data' => $livestocks->map(function ($livestock, $index) {
                        return [
                            'No' => $index + 1,
                            'ID Tag' => $livestock->tag_id,
                            'Nama' => $livestock->name,
                            'Spesies' => $livestock->breed->species->name ?? 'T/A',
                            'Ras' => $livestock->breed->name ?? 'T/A',
                            'Tanggal Lahir' => $livestock->birthdate,
                            'Jenis Kelamin' => $livestock->sex,
                            'Status' => $livestock->status->value ?? $livestock->status,
                            'Berat Saat Ini (kg)' => $livestock->weight ?? 'T/A',
                        ];
                    }),
                ];

            case 'feeding-report':
                $feedings = $herdQuery()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->with(['herd', 'ration', 'user'])
                    ->get();

                return [
                    'title' => 'Laporan Pemberian Pakan',
                    'period' => \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y'),
                    'data' => $feedings->map(function ($feeding, $index) {
                        return [
                            'No' => $index + 1,
                            'Tanggal' => $feeding->date,
                            'Kandang' => $feeding->herd->name ?? 'T/A',
                            'Ransum' => $feeding->ration->name ?? 'T/A',
                            'Jumlah' => $feeding->quantity,
                            'Sesi' => $feeding->session,
                            'Catatan' => $feeding->notes,
                        ];
                    }),
                ];

            case 'milking-report':
                $milkings = LivestockMilking::whereHas('livestock', function ($query) use ($farmId) {
                        $query->where('farm_id', $farmId);
                    })
                    ->whereBetween('date', [$startDate, $endDate])
                    ->when($livestockId, function ($query) use ($livestockId) {
                        return $query->where('livestock_id', $livestockId);
                    })
                    ->with('livestock')
                    ->get();

                return [
                    'title' => 'Laporan Produksi Susu',
                    'period' => \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y'),
                    'data' => $milkings->map(function ($milking, $index) {
                        return [
                            'No' => $index + 1,
                            'Tanggal' => $milking->date,
                            'Ternak' => $milking->livestock->tag_id . ' - ' . $milking->livestock->name,
                            'Volume Susu' => $milking->milk_volume,
                            'Sesi' => $milking->session,
                            'Catatan' => $milking->notes,
                        ];
                    }),
                ];

            case 'weight-report':
                $weights = LivestockWeight::whereHas('livestock', function ($query) use ($farmId) {
                        $query->where('farm_id', $farmId);
                    })
                    ->whereBetween('date', [$startDate, $endDate])
                    ->when($livestockId, function ($query) use ($livestockId) {
                        return $query->where('livestock_id', $livestockId);
                    })
                    ->with('livestock')
                    ->get();

                return [
                    'title' => 'Laporan Perkembangan Bobot',
                    'period' => \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y'),
                    'data' => $weights->map(function ($weight, $index) {
                        return [
                            'No' => $index + 1,
                            'Tanggal' => $weight->date,
                            'Ternak' => $weight->livestock->tag_id . ' - ' . $weight->livestock->name,
                            'Berat (kg)' => $weight->weight,
                        ];
                    }),
                ];

            default:
                return [
                    'title' => 'Laporan ' . ucfirst($type),
                    'period' => \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y'),
                    'data' => [],
                ];
        }
    }

    private function generatePdfReport(Report $report, array $data): string
    {
        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'data' => $data,
        ]);

        $filename = 'reports/' . $report->type . '_' . $report->id . '_' . time() . '.pdf';
        Storage::put($filename, $pdf->output());

        return $filename;
    }

    private function generateExcelReport(Report $report, array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $data['title']);
        $sheet->setCellValue('A2', 'Periode: ' . $data['period']);
        $sheet->setCellValue('A3', 'Dibuat: ' . now()->format('Y-m-d H:i:s'));

        if (empty($data['data'])) {
            $sheet->setCellValue('A5', 'Tidak ada data untuk periode ini.');
        } else {
            $headers = array_keys((array) $data['data']->first());
            $row = 5;

            // Headers
            foreach ($headers as $col => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue($columnLetter . $row, $header);
            }

            // Data
            foreach ($data['data'] as $item) {
                $row++;
                $itemArray = (array) $item;
                foreach ($headers as $col => $header) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $value = $itemArray[$header] ?? '';
                    $sheet->setCellValue($columnLetter . $row, is_string($value) || is_numeric($value) ? $value : (string) $value);
                }
            }
        }

        $filename = 'reports/' . $report->type . '_' . $report->id . '_' . time() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        
        Storage::put($filename, $content);

        return $filename;
    }

    private function generateCsvReport(Report $report, array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $data['title']);
        $sheet->setCellValue('A2', 'Periode: ' . $data['period']);
        $sheet->setCellValue('A3', 'Dibuat: ' . now()->format('Y-m-d H:i:s'));

        if (empty($data['data'])) {
            $sheet->setCellValue('A5', 'Tidak ada data untuk periode ini.');
        } else {
            $headers = array_keys((array) $data['data']->first());
            $row = 5;

            // Headers
            foreach ($headers as $col => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue($columnLetter . $row, $header);
            }

            // Data
            foreach ($data['data'] as $item) {
                $row++;
                $itemArray = (array) $item;
                foreach ($headers as $col => $header) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $value = $itemArray[$header] ?? '';
                    $sheet->setCellValue($columnLetter . $row, is_string($value) || is_numeric($value) ? $value : (string) $value);
                }
            }
        }

        $filename = 'reports/' . $report->type . '_' . $report->id . '_' . time() . '.csv';
        $writer = new Csv($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        
        Storage::put($filename, $content);

        return $filename;
    }

    private function generateReportName(string $type, string $startDate, string $endDate): string
    {
        $typeNames = [
            'livestock-summary' => 'Ringkasan_Ternak',
            'feeding-report' => 'Laporan_Pakan',
            'milking-report' => 'Laporan_Susu',
            'weight-report' => 'Laporan_Bobot',
            'health-report' => 'Laporan_Kesehatan',
            'productivity-report' => 'Laporan_Produktivitas',
            'financial-report' => 'Laporan_Keuangan',
            'breeding-report' => 'Laporan_Perkawinan',
        ];

        $name = $typeNames[$type] ?? $type;
        return $name . '_' . $startDate . '_to_' . $endDate;
    }

    private function getFileExtension(string $format): string
    {
        return match($format) {
            'excel' => 'xlsx',
            'csv' => 'csv',
            'pdf' => 'pdf',
            default => 'txt'
        };
    }
}