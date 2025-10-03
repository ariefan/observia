<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $data['title'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .logo-header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #14b8a6;
        }
        .logo-left, .logo-right {
            display: table-cell;
            vertical-align: middle;
        }
        .logo-left {
            width: 30%;
            text-align: left;
        }
        .logo-right {
            width: 30%;
            text-align: right;
        }
        .logo-center {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: middle;
        }
        .app-logo {
            max-height: 40px;
            width: auto;
        }
        .farm-logo {
            max-height: 40px;
            width: auto;
            margin-bottom: 5px;
        }
        .farm-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        .period {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #14b8a6;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-left: 4px solid #14b8a6;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 220px;
        }
        .content-section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .no-data {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 40px;
        }
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 50px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #6b7280;
            display: table;
            width: 100%;
            padding: 15px 0;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
            width: 50%;
            vertical-align: middle;
            padding-left: 0px;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            width: 50%;
            vertical-align: middle;
            padding-right: 0px;
        }
        .report-end {
            margin-top: 40px;
            padding: 20px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .pagenum:before {
            content: counter(page);
        }
        .summary-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            background-color: #f1f5f9;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            width: 30%;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #14b8a6;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    @php
        $user = auth()->user();
        $farm = null;
        if ($user && $user->current_farm_id) {
            $farm = \App\Models\Farm::find($user->current_farm_id);
        }
    @endphp
    <!-- Logo Header -->
    <div class="logo-header">
        <div class="logo-left">
            @php
                // Find the app logo in build assets
                $logoFiles = glob(public_path('build/assets/logo-*.png'));
                $appLogoPath = !empty($logoFiles) ? $logoFiles[0] : null;
                $appLogoBase64 = null;
                if ($appLogoPath && file_exists($appLogoPath)) {
                    $appLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($appLogoPath));
                }
            @endphp
            @if($appLogoBase64)
                <img src="{{ $appLogoBase64 }}" alt="App Logo" class="app-logo">
            @endif
        </div>
        <div class="logo-center">
            <div class="title">{{ $data['title'] }}</div>
            @if($farm)
                <div class="farm-name">{{ $farm->name }}</div>
                <div class="period">{{ $data['period'] }}</div>
            @endif
        </div>
        <div class="logo-right">
            {{-- This space is now empty --}}
        </div>
    </div>

    <!-- Report Information -->
    <div class="info-section">
        @if($report->filters && isset($report->filters['livestock_id']))
        <div class="info-row">
            <span class="label">Filter Ternak</span>
            ID {{ $report->filters['livestock_id'] }}
        </div>
        @endif

        <!-- Report Type Specific Content -->
        @switch($report->type)
            @case('livestock-summary')
                @if(!empty($data['data']) && count($data['data']) > 0)
                    @php
                        $totalAnimals = count($data['data']);
                        $maleCount = $data['data']->where('Jenis Kelamin', 'male')->count();
                        $femaleCount = $data['data']->where('Jenis Kelamin', 'female')->count();
                        $activeCount = $data['data']->where('Status', 'active')->count();
                    @endphp
                    <div class="info-row">
                        <span class="label">Total Ternak</span>
                        {{ $totalAnimals }} ekor
                    </div>
                    <div class="info-row">
                        <span class="label">Jantan</span>
                        {{ $maleCount }} ekor ({{ $totalAnimals > 0 ? round($maleCount/$totalAnimals*100, 1) : 0 }}%)
                    </div>
                    <div class="info-row">
                        <span class="label">Betina</span>
                        {{ $femaleCount }} ekor ({{ $totalAnimals > 0 ? round($femaleCount/$totalAnimals*100, 1) : 0 }}%)
                    </div>
                    <div class="info-row">
                        <span class="label">Status Aktif</span>
                        {{ $activeCount }} ekor ({{ $totalAnimals > 0 ? round($activeCount/$totalAnimals*100, 1) : 0 }}%)
                    </div>
                @endif
                @break

            @case('milking-report')
                @if(!empty($data['data']) && count($data['data']) > 0)
                    @php
                        $totalVolume = $data['data']->sum('Volume Susu');
                        $recordCount = count($data['data']);
                        $livestockCount = $data['data']->unique('Ternak')->count();

                        // Calculate number of days
                        $uniqueDays = $data['data']->unique('Tanggal')->count();

                        // Calculate average per day: total volume / number of days
                        $avgDailyVolume = $uniqueDays > 0 ? $totalVolume / $uniqueDays : 0;

                        // Calculate average daily production per livestock
                        $avgDailyPerLivestock = ($uniqueDays > 0 && $livestockCount > 0)
                            ? $totalVolume / ($uniqueDays * $livestockCount)
                            : 0;

                        // Calculate highest daily production per livestock
                        // Group by livestock and date, sum the volume, then get the maximum
                        $maxDailyPerLivestock = $data['data']
                            ->groupBy(function($item) {
                                return $item['Ternak'] . '|' . $item['Tanggal'];
                            })
                            ->map(function($group) {
                                return $group->sum('Volume Susu');
                            })
                            ->max();
                    @endphp
                    <div class="info-row">
                        <span class="label">Jumlah Ternak</span>
                        {{ $livestockCount }} ekor
                    </div>
                    <div class="info-row">
                        <span class="label">Jumlah Hari</span>
                        {{ $uniqueDays }} hari
                    </div>
                    <div class="info-row">
                        <span class="label">Total Produksi</span>
                        {{ number_format($totalVolume, 2) }} liter
                    </div>
                    <div class="info-row">
                        <span class="label">Rata-rata Harian</span>
                        {{ number_format($avgDailyVolume, 2) }} liter/hari
                    </div>
                    <div class="info-row">
                        <span class="label">Rata-rata Harian Ternak</span>
                        {{ number_format($avgDailyPerLivestock, 2) }} liter/hari
                    </div>
                    <div class="info-row">
                        <span class="label">Produksi Susu Harian Tertinggi</span>
                        {{ number_format($maxDailyPerLivestock ?? 0, 2) }} liter
                    </div>
                @endif
                @break

            @case('feeding-report')
                @if(!empty($data['data']) && count($data['data']) > 0)
                    @php
                        $totalFeedings = count($data['data']);
                        $uniqueFeeds = $data['data']->unique('Ransum')->count();
                        $totalQuantity = $data['data']->sum('Jumlah');
                    @endphp
                    <div class="info-row">
                        <span class="label">Total Pemberian</span>
                        {{ $totalFeedings }} kali
                    </div>
                    <div class="info-row">
                        <span class="label">Jenis Pakan</span>
                        {{ $uniqueFeeds }} jenis
                    </div>
                    <div class="info-row">
                        <span class="label">Total Kuantitas</span>
                        {{ number_format($totalQuantity, 2) }}
                    </div>
                @endif
            @break
        @endswitch
    </div>

    <!-- Main Content -->
    <div class="content-section">
        @if(empty($data['data']) || count($data['data']) == 0)
            <div class="no-data">
                Tidak ada data untuk periode {{ $data['period'] }}
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        @php
                            $firstItem = $data['data']->first();
                            $headers = is_array($firstItem) ? array_keys($firstItem) : array_keys($firstItem->toArray());
                        @endphp
                        @foreach($headers as $header)
                            <th>{{ ucfirst(str_replace('_', ' ', $header)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['data'] as $item)
                        @php
                            $itemArray = is_array($item) ? $item : $item->toArray();
                        @endphp
                        <tr>
                            @foreach($headers as $header)
                                <td>
                                    @if ($header === 'Tanggal')
                                        {{ \Carbon\Carbon::parse($itemArray[$header])->format('d M Y') }}
                                    @elseif ($header === 'Sesi')
                                        @if ($itemArray[$header] === 'morning')
                                            Pagi
                                        @elseif ($itemArray[$header] === 'afternoon')
                                            Sore
                                        @else
                                            {{ $itemArray[$header] }}
                                        @endif
                                    @else
                                        {{ $itemArray[$header] ?? '-' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Report End Info -->
    <div class="report-end">
        <!-- Laporan diperbarui otomatis oleh Sistem Aifarm tanggal {{ now()->format('d/m/Y') }} jam {{ now()->format('H:i:s') }} ~ Dibuat oleh {{ auth()->user()->name ?? 'System' }} -->
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            <!-- {{ now()->format('d M Y H:i') }} -->
              Laporan diperbarui otomatis oleh Sistem Aifarm tanggal {{ now()->format('d/m/Y') }} jam {{ now()->format('H:i:s') }} ~ Dibuat oleh {{ auth()->user()->name ?? 'System' }}
        </div>
        <div class="footer-right">
            Halaman <span class="pagenum"></span>
        </div>
    </div>
</body>
</html>
