<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $livestock->name }} - {{ $livestock->breed->species->name ?? 'Ternak' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-6xl">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $livestock->name }}</h1>
                    <div class="flex items-center gap-4 mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $livestock->tag_id }}
                        </span>
                        <span class="text-gray-600">{{ $livestock->breed->species->name ?? 'Ternak' }} - {{ $livestock->breed->name ?? 'Unknown' }}</span>
                        @if($livestock->sex == 'M')
                            <span class="text-blue-500">♂ Jantan</span>
                        @else
                            <span class="text-pink-500">♀ Betina</span>
                        @endif
                    </div>
                    
                    @if($livestock->farm)
                        <div class="text-sm text-gray-600">
                            <div class="flex items-center gap-2 mb-1">
                                @if($livestock->farm->image)
                                    <img src="{{ asset('storage/' . $livestock->farm->image) }}" alt="{{ $livestock->farm->name }}" class="h-8 w-8 object-cover rounded">
                                @endif
                                <strong>{{ $livestock->farm->name }}</strong>
                            </div>
                            <p>{{ $livestock->farm->address }}</p>
                            @if($livestock->farm->owner)
                                <p>Pemilik: {{ $livestock->farm->owner->name }}</p>
                            @endif
                        </div>
                    @endif
                </div>
                
                <div class="text-right">
                    <img src="{{ Vite::asset('resources/js/assets/logo.png') }}" alt="Aifarm" class="h-12 w-auto mb-2">
                    <p class="text-sm text-gray-500">Powered by Aifarm.id</p>
                </div>
            </div>
        </div>

        <!-- Photos Section -->
        @if($livestock->photo && count($livestock->photo) > 0)
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Foto Ternak</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($livestock->photo as $photo)
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <img src="{{ asset('storage/' . $photo) }}" alt="Foto {{ $livestock->name }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Age -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Umur</h3>
                    <p class="text-3xl font-bold text-teal-600">
                        {{ $livestock->age_in_year ?? 0 }} <span class="text-lg font-normal">th</span>
                        {{ ($livestock->age_in_month ?? 0) % 12 }} <span class="text-lg font-normal">bl</span>
                    </p>
                </div>
            </div>

            <!-- Weight -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Bobot</h3>
                    <p class="text-3xl font-bold text-cyan-600">
                        {{ $livestock->weight ?? 0 }} <span class="text-lg font-normal">kg</span>
                    </p>
                </div>
            </div>

            <!-- Birth Date -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tanggal Lahir</h3>
                    <p class="text-lg text-gray-600">
                        {{ $livestock->birthdate ? \Carbon\Carbon::parse($livestock->birthdate)->format('d F Y') : 'Tidak diketahui' }}
                    </p>
                </div>
            </div>

            <!-- Lactation Days (for females only) -->
            @if($livestock->sex == 'F')
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Hari Laktasi</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            {{ $lactationDays }} <span class="text-lg font-normal">hari</span>
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Charts Section (for females with milking data) -->
        @if($livestock->sex == 'F' && count($milkingHistory) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Milking Chart -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rata-rata Produksi Susu</h3>
                    <div class="chart-container">
                        <canvas id="milkingChart"></canvas>
                    </div>
                </div>

                <!-- Weight Chart -->
                @if(count($weightHistory) > 0)
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Perkembangan Bobot</h3>
                        <div class="chart-container">
                            <canvas id="weightChart"></canvas>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Pedigree Section -->
        @if(count($pedigreeData) > 1)
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Silsilah Keturunan</h2>
                <div class="overflow-x-auto">
                    @include('certificate.pedigree-print', ['pedigreeData' => $pedigreeData])
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="text-center text-sm text-gray-500 mt-8 py-4 border-t">
            <p>Data ternak yang sah dari record Aifarm.id - Indonesia</p>
            <p>Diakses pada {{ \Carbon\Carbon::now()->format('d F Y') }} pukul {{ \Carbon\Carbon::now()->format('H:i') }} WIB</p>
        </div>
    </div>

    <!-- Charts Script -->
    <script>
        // Milking Chart
        @if($livestock->sex == 'F' && count($milkingHistory) > 0)
            const milkingData = {!! json_encode($milkingHistory) !!};
            const milkingLabels = milkingData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
            });
            const milkingVolumes = milkingData.map(item => parseFloat(item.average_volume || 0));

            const milkingCtx = document.getElementById('milkingChart').getContext('2d');
            new Chart(milkingCtx, {
                type: 'line',
                data: {
                    labels: milkingLabels,
                    datasets: [{
                        label: 'Rata-rata Produksi (liter/hari)',
                        data: milkingVolumes,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif

        // Weight Chart
        @if(count($weightHistory) > 0)
            const weightData = {!! json_encode($weightHistory) !!};
            const weightLabels = weightData.map(item => {
                const [year, month] = item.month.split('-');
                const date = new Date(year, month - 1);
                return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
            });
            const weightValues = weightData.map(item => parseFloat(item.average_weight || 0));

            const weightCtx = document.getElementById('weightChart').getContext('2d');
            new Chart(weightCtx, {
                type: 'line',
                data: {
                    labels: weightLabels,
                    datasets: [{
                        label: 'Bobot Rata-rata (kg)',
                        data: weightValues,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
    </script>
</body>
</html>