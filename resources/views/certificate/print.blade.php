<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Registrasi - {{ $livestock->tag_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            width: 210mm;
            min-height: 297mm;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
            .avoid-page-break {
                page-break-inside: avoid;
            }
            /* Ensure only card background colors and borders print */
            .bg-teal-200\/10 {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: rgba(153, 246, 228, 0.1) !important;
            }
            .border-teal-500 {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
                border-color: #14b8a6 !important;
            }
            /* Keep page background white for print */
            .bg-gradient-to-b {
                background: white !important;
            }
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
                background-color: transparent !important;
            }
            .pedigree-container .bg-gray-50, 
            .pedigree-container .bg-transparent { 
                background-color: transparent !important; 
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button onclick="window.print()" class="print-button no-print">🖨️ Print Certificate</button>

    <div class="min-h-screen bg-gradient-to-b from-teal-50 to-white p-2">
        <div class="bg-teal-200/10 rounded-2xl border border-teal-500 p-8 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-lg font-bold text-teal-700 mt-6">Sertifikat Registrasi</h1>
                    <div class="flex items-center gap-4">
                        @if($farm && $farm->image)
                            <img src="{{ asset('storage/' . $farm->image) }}" alt="{{ $farm->name }}" class="h-16 w-16 object-cover rounded-lg">
                        @endif
                        <p class="text-3xl font-semibold">{{ $farm->name ?? 'Farm' }}</p>
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ $farm->address ?? 'Alamat tidak tersedia' }}
                        @if($farm && $farm->owner)
                            | {{ $farm->owner->name }}
                        @endif
                        @if($farm && $farm->phone)
                            | {{ $farm->phone }}
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <img src="{{ Vite::asset('resources/js/assets/logo.png') }}" alt="Aifarm" class="h-12 w-auto">
                </div>
            </div>

            <!-- Info section -->
            <div class="grid grid-cols-2 gap-6 mt-8 text-sm">
                <div>
                    <p><span class="font-semibold">Nama:</span> {{ $livestock->name }}</p>
                    <p><span class="font-semibold">Jenis {{ $livestock->breed->species->name ?? 'Hewan' }}:</span> {{ $livestock->breed->name ?? 'Tidak diketahui' }}</p>
                    <p><span class="font-semibold">Jenis Kelamin:</span> {{ $livestock->sex === 'M' ? 'Jantan' : 'Betina' }}</p>
                </div>
                <div>
                    <p><span class="font-semibold">Tanggal Lahir:</span> {{ $livestock->birthdate ? \Carbon\Carbon::parse($livestock->birthdate)->format('d F Y') : 'Tidak diketahui' }}</p>
                    <p><span class="font-semibold">Aifarm ID:</span> {{ $livestock->tag_id }}</p>
                    <p><span class="font-semibold">Nomor Ternak:</span> {{ $livestock->tag_id }}</p>
                </div>
            </div>

            <!-- Pedigree Tree -->
            <div class="mt-20">
                <div class="flex flex-col items-center">
                    <div class="pedigree-container w-full mt-10">
                        <!-- Include the exact pedigree component -->
                        @include('certificate.pedigree-print', ['pedigreeData' => $pedigreeData])
                    </div>
                </div>
                
                <!-- QR Code -->
                <div class="flex justify-end mt-6">
                    <div class="text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(url('/livestocks/' . $livestock->id)) }}" 
                             alt="QR Code" 
                             class="w-20 h-20 border border-gray-300 rounded">
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 border-t border-teal-200 pt-4 text-xs text-gray-600 text-center">
                Di atas merupakan kutipan yang sah dari record Aifarm.id - Indonesia.<br />
                Pada tanggal {{ \Carbon\Carbon::now()->format('d F Y') }} pukul {{ \Carbon\Carbon::now()->format('H:i') }} WIB.
            </div>
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