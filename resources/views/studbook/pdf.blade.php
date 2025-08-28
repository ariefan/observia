<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Studbook - {{ $livestock->tag_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #14b8a6;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #14b8a6;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }
        .farm-info {
            font-size: 14px;
            color: #888;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #14b8a6;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 5px 10px 5px 0;
            vertical-align: top;
        }
        .info-value {
            display: table-cell;
            padding: 5px 0;
            vertical-align: top;
        }
        .pedigree-section {
            margin-top: 30px;
            page-break-before: always;
        }
        .pedigree-diagram {
            margin: 20px 0;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .page-number:before {
            content: counter(page);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="title">STUDBOOK</div>
        <div class="subtitle">{{ $livestock->tag_id }} - {{ $livestock->name }}</div>
        @if($farm)
        <div class="farm-info">{{ $farm->name }} | {{ $farm->address ?? 'Alamat tidak tersedia' }}</div>
        @endif
    </div>

    <!-- Livestock Information -->
    <div class="section">
        <div class="section-title">Informasi Ternak</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Tag ID:</div>
                <div class="info-value">{{ $livestock->tag_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nama:</div>
                <div class="info-value">{{ $livestock->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Spesies:</div>
                <div class="info-value">{{ $livestock->breed->species->name ?? 'Tidak diketahui' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Ras:</div>
                <div class="info-value">{{ $livestock->breed->name ?? 'Tidak diketahui' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jenis Kelamin:</div>
                <div class="info-value">{{ $livestock->sex === 'M' ? 'Jantan' : 'Betina' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Lahir:</div>
                <div class="info-value">{{ $livestock->birthdate ? \Carbon\Carbon::parse($livestock->birthdate)->format('d F Y') : 'Tidak diketahui' }}</div>
            </div>
            @if($livestock->maleParent)
            <div class="info-row">
                <div class="info-label">Ayah:</div>
                <div class="info-value">{{ $livestock->maleParent->tag_id }} - {{ $livestock->maleParent->name }}</div>
            </div>
            @endif
            @if($livestock->femaleParent)
            <div class="info-row">
                <div class="info-label">Ibu:</div>
                <div class="info-value">{{ $livestock->femaleParent->tag_id }} - {{ $livestock->femaleParent->name }}</div>
            </div>
            @endif
            @if($livestock->herd)
            <div class="info-row">
                <div class="info-label">Kelompok:</div>
                <div class="info-value">{{ $livestock->herd->name }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Farm Information -->
    @if($farm)
    <div class="section">
        <div class="section-title">Informasi Peternakan</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nama Peternakan:</div>
                <div class="info-value">{{ $farm->name }}</div>
            </div>
            @if($farm->address)
            <div class="info-row">
                <div class="info-label">Alamat:</div>
                <div class="info-value">{{ $farm->address }}</div>
            </div>
            @endif
            @if($farm->phone)
            <div class="info-row">
                <div class="info-label">Telepon:</div>
                <div class="info-value">{{ $farm->phone }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Pedigree Diagram -->
    <div class="pedigree-section">
        <div class="section-title">Silsilah (Pedigree)</div>
        <div class="pedigree-diagram">
            @if($pedigreeImagePath && file_exists($pedigreeImagePath))
                <!-- Embed captured pedigree image -->
                <div style="text-align: center; margin: 20px 0;">
                    <img src="{{ $pedigreeImagePath }}" alt="Pedigree Diagram" style="max-width: 100%; height: auto; border: 1px solid #e5e7eb; border-radius: 8px;" />
                </div>
            @else
                <!-- Fallback to HTML version if image capture fails -->
                @include('studbook.pedigree-exact', ['pedigreeData' => $pedigreeData])
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Studbook generated on {{ now()->format('d F Y \a\t H:i') }} | AI Farm Management System</p>
        <p>Halaman <span class="page-number"></span></p>
    </div>
</body>
</html>