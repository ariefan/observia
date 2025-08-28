<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Studbook - {{ $livestock->tag_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        /* Print styles */
        @media print {
            body {
                padding: 15px;
                font-size: 11px;
            }
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
            .avoid-page-break {
                page-break-inside: avoid;
            }
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
        }
        .pedigree-container {
            margin: 20px 0;
            overflow: visible;
        }
        
        /* Print button styles */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #14b8a6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .print-button:hover {
            background: #0f9488;
        }
        
        /* Ensure pedigree component styles work for print */
        .pedigree-container .bg-gray-50 { background-color: #f9fafb !important; }
        .pedigree-container .bg-white { background-color: #ffffff !important; }
        .pedigree-container .bg-blue-100 { background-color: #dbeafe !important; }
        .pedigree-container .bg-gray-300 { background-color: #d1d5db !important; }
        .pedigree-container .border-gray-200 { border-color: #e5e7eb !important; }
        .pedigree-container .text-gray-900 { color: #111827 !important; }
        .pedigree-container .text-gray-600 { color: #4b5563 !important; }
        .pedigree-container .text-gray-500 { color: #6b7280 !important; }
        .pedigree-container .text-gray-400 { color: #9ca3af !important; }
        
        @media print {
            .pedigree-container {
                background-color: white !important;
            }
            .pedigree-container .bg-gray-50 { 
                background-color: white !important; 
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button onclick="window.print()" class="print-button no-print">🖨️ Print Studbook</button>

    <!-- Header -->
    <div class="header avoid-page-break">
        <div class="title">STUDBOOK</div>
        <div class="subtitle">{{ $livestock->tag_id }} - {{ $livestock->name }}</div>
        @if($farm)
        <div class="farm-info">{{ $farm->name }} | {{ $farm->address ?? 'Alamat tidak tersedia' }}</div>
        @endif
    </div>

    <!-- Livestock Information -->
    <div class="section avoid-page-break">
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
    <div class="section avoid-page-break">
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
    <div class="pedigree-section page-break">
        <div class="section-title">Silsilah (Pedigree)</div>
        <div class="pedigree-container">
            <!-- Include the exact pedigree component -->
            @include('studbook.pedigree-print', ['pedigreeData' => $pedigreeData])
        </div>
    </div>

    <script>
        // Auto-focus print dialog when page loads
        window.addEventListener('load', function() {
            setTimeout(() => window.print(), 500);
        });
        
        // Handle after print to close window (optional)
        window.addEventListener('afterprint', function() {
            // Uncomment the line below if you want to auto-close after printing
            // window.close();
        });
    </script>
</body>
</html>