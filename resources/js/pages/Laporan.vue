<template>

  <Head title="Laporan" />

  <AppLayout>
    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <SecondSidebar current-route="laporan" />

      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-start justify-between space-x-4">
        <div class="space-y-1">
          <h1 class="text-2xl font-bold tracking-tight">Laporan</h1>
          <p class="text-muted-foreground">
            Buat dan unduh laporan berbagai data peternakan Anda
          </p>
        </div>
      </div>

      <!-- Report Generation Form -->
      <Card class="max-w-7xl">
        <CardHeader>
          <CardTitle>Buat Laporan</CardTitle>
          <CardDescription>
            Pilih jenis laporan dan rentang tanggal untuk membuat laporan yang diinginkan
          </CardDescription>
        </CardHeader>

        <CardContent class="space-y-6">
          <!-- Report Type Selection -->
          <div class="space-y-2">
            <Label for="report-type">Jenis Laporan</Label>
            <Select v-model="reportForm.type">
              <SelectTrigger id="report-type">
                <SelectValue placeholder="Pilih jenis laporan" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="livestock-summary">Ringkasan Ternak</SelectItem>
                <SelectItem value="feeding-report">Laporan Pemberian Pakan</SelectItem>
                <SelectItem value="milking-report">Laporan Produksi Susu</SelectItem>
                <SelectItem value="weight-report">Laporan Perkembangan Bobot</SelectItem>
                <SelectItem value="health-report">Laporan Kesehatan Ternak</SelectItem>
                <SelectItem value="productivity-report">Laporan Produktivitas</SelectItem>
                <SelectItem value="financial-report">Laporan Keuangan</SelectItem>
                <SelectItem value="breeding-report">Laporan Perkawinan</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Date Range Selection -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="start-date">Tanggal Mulai</Label>
              <Input id="start-date" v-model="reportForm.startDate" type="date" :max="reportForm.endDate || today" />
            </div>
            <div class="space-y-2">
              <Label for="end-date">Tanggal Selesai</Label>
              <Input id="end-date" v-model="reportForm.endDate" type="date" :min="reportForm.startDate" :max="today" />
            </div>
          </div>

          <!-- Additional Filters -->
          <div class="space-y-4" v-if="reportForm.type">
            <Separator />
            <h4 class="font-medium">Filter Tambahan</h4>

            <!-- Livestock Filter (for applicable reports) -->
            <div v-if="needsLivestockFilter" class="space-y-2">
              <Label for="livestock-filter">Ternak Spesifik (Opsional)</Label>
              <Select v-model="reportForm.livestockId">
                <SelectTrigger id="livestock-filter">
                  <SelectValue placeholder="Pilih ternak (kosongkan untuk semua ternak)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Ternak</SelectItem>
                  <SelectItem v-for="livestock in sampleLivestock" :key="livestock.id" :value="livestock.id.toString()">
                    {{ livestock.name }} ({{ livestock.tag_id }})
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Format Selection -->
            <div class="space-y-2">
              <Label for="format">Format Laporan</Label>
              <Select v-model="reportForm.format">
                <SelectTrigger id="format">
                  <SelectValue placeholder="Pilih format" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pdf">PDF</SelectItem>
                  <SelectItem value="excel">Excel (.xlsx)</SelectItem>
                  <SelectItem value="csv">CSV</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Report Description -->
          <div v-if="reportDescription" class="p-4 bg-muted rounded-lg">
            <h5 class="font-medium mb-2">Deskripsi Laporan</h5>
            <p class="text-sm text-muted-foreground">{{ reportDescription }}</p>
          </div>
        </CardContent>

        <CardFooter class="flex gap-2">
          <Button @click="generateReport" :disabled="!canGenerate || isGenerating" class="flex-1">
            <FileDown v-if="!isGenerating" class="h-4 w-4 mr-2" />
            <Loader2 v-else class="h-4 w-4 mr-2 animate-spin" />
            {{ isGenerating ? 'Membuat Laporan...' : 'Buat & Unduh Laporan' }}
          </Button>
          <Button @click="resetForm" variant="outline">
            Reset
          </Button>
        </CardFooter>
      </Card>

      <!-- Recent Reports -->
      <Card class="max-w-7xl" v-if="recentReports.length > 0">
        <CardHeader>
          <CardTitle>Laporan Terbaru</CardTitle>
          <CardDescription>
            Laporan yang telah dibuat dalam 30 hari terakhir
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="space-y-3">
            <div v-for="(report, index) in recentReports" :key="report.id"
              class="flex items-center justify-between p-3 border rounded-lg">
              <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-medium">
                    {{ index + 1 }}
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <FileText class="h-5 w-5 text-muted-foreground" />
                </div>
                <div>
                  <p class="font-medium text-sm">{{ report.name }}</p>
                  <p class="text-xs text-muted-foreground">
                    {{ formatDate(report.created_at) }} • {{ report.format.toUpperCase() }}
                  </p>
                </div>
              </div>
              <div class="flex gap-2">
                <Button @click="downloadReport(report)" size="sm" variant="outline">
                  <Download class="h-3 w-3 mr-1" />
                  Unduh
                </Button>
                <Button @click="deleteReport(report.id)" size="sm" variant="ghost">
                  <Trash2 class="h-3 w-3" />
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl">
        <Card>
          <CardContent class="p-4 flex items-center gap-3">
            <FileText class="h-8 w-8 text-blue-500" />
            <div>
              <p class="text-sm text-muted-foreground">Total Laporan</p>
              <p class="text-2xl font-bold">{{ totalReports }}</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4 flex items-center gap-3">
            <Calendar class="h-8 w-8 text-green-500" />
            <div>
              <p class="text-sm text-muted-foreground">Bulan Ini</p>
              <p class="text-2xl font-bold">{{ monthlyReports }}</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4 flex items-center gap-3">
            <Download class="h-8 w-8 text-purple-500" />
            <div>
              <p class="text-sm text-muted-foreground">Unduhan</p>
              <p class="text-2xl font-bold">{{ totalDownloads }}</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { FileDown, FileText, Download, Trash2, Calendar, Loader2 } from 'lucide-vue-next';
import axios from 'axios';
import SecondSidebar from '@/components/SecondSidebar.vue';

// Definisi tipe
interface Livestock {
  id: number;
  name: string;
  tag_id: string;
}

interface Report {
  id: number;
  name: string;
  created_at: string;
  format: string;
  download_url?: string;
}

type ReportType = 'livestock-summary' | 'feeding-report' | 'milking-report' | 'weight-report' | 'health-report' | 'productivity-report' | 'financial-report' | 'breeding-report';

// State form
const reportForm = ref({
  type: '',
  startDate: '',
  endDate: '',
  livestockId: 'all',
  format: 'pdf',
});

// State UI
const isGenerating = ref(false);

// Tanggal saat ini untuk validasi tanggal maksimum
const today = new Date().toISOString().split('T')[0];

// Data dari API
const sampleLivestock = ref<Livestock[]>([]);
const recentReports = ref<Report[]>([]);
const totalReports = ref(0);
const monthlyReports = ref(0);
const totalDownloads = ref(0);

// Deskripsi laporan
const reportDescriptions: Record<ReportType, string> = {
  'livestock-summary': 'Ringkasan lengkap semua ternak termasuk informasi dasar, status kesehatan, dan produktivitas.',
  'feeding-report': 'Detail pemberian pakan harian termasuk jenis pakan, jumlah, dan jadwal pemberian.',
  'milking-report': 'Laporan produksi susu harian, mingguan, dan bulanan dengan analisis tren.',
  'weight-report': 'Perkembangan bobot ternak dari waktu ke waktu dengan grafik pertumbuhan.',
  'health-report': 'Riwayat kesehatan ternak, vaksinasi, dan perawatan medis yang diberikan.',
  'productivity-report': 'Analisis produktivitas ternak berdasarkan produksi susu, pertambahan bobot, dan efisiensi pakan.',
  'financial-report': 'Laporan keuangan terkait biaya pakan, perawatan, dan pendapatan dari produksi.',
  'breeding-report': 'Laporan perkawinan, kebuntingan, dan kelahiran ternak.',
};

// Properti computed
const needsLivestockFilter = computed(() => {
  return ['feeding-report', 'milking-report', 'weight-report', 'health-report'].includes(reportForm.value.type);
});

const reportDescription = computed(() => {
  return reportForm.value.type ? reportDescriptions[reportForm.value.type as ReportType] || '' : '';
});

const canGenerate = computed(() => {
  return reportForm.value.type && reportForm.value.startDate && reportForm.value.endDate && reportForm.value.format;
});

// Fungsi API
const fetchReports = async () => {
  try {
    const response = await axios.get('/reports');
    recentReports.value = response.data.reports || [];
    totalReports.value = response.data.stats?.total_reports || 0;
    monthlyReports.value = response.data.stats?.monthly_reports || 0;
    totalDownloads.value = response.data.stats?.total_downloads || 0;
  } catch (error) {
    console.error('Kesalahan mengambil laporan:', error);
  }
};

const fetchLivestock = async () => {
  try {
    const response = await axios.get('/api/livestocks');
    sampleLivestock.value = response.data || [];
  } catch (error) {
    console.error('Kesalahan mengambil data ternak:', error);
  }
};

// Fungsi-fungsi
const generateReport = async () => {
  if (!canGenerate.value) return;

  isGenerating.value = true;

  const payload = {
    type: reportForm.value.type,
    format: reportForm.value.format,
    start_date: reportForm.value.startDate,
    end_date: reportForm.value.endDate,
    ...(reportForm.value.livestockId && reportForm.value.livestockId !== 'all' && { livestock_id: reportForm.value.livestockId })
  };

  // Use axios for file downloads with proper CSRF handling
  try {
    const response = await axios.post('/reports/generate', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      }
    });

    if (response.data.report && response.data.report.download_url) {
      // Download the generated report
      const downloadLink = document.createElement('a');
      downloadLink.href = response.data.report.download_url;
      downloadLink.download = ''; // Force download
      downloadLink.click();

      // Refresh the reports list
      await fetchReports();
    } else {
      alert('Laporan berhasil dibuat tetapi gagal diunduh. Silakan cek daftar laporan.');
      await fetchReports();
    }
  } catch (error) {
    console.error('Kesalahan membuat laporan:', error);
    alert('Gagal membuat laporan. Silakan coba lagi.');
  } finally {
    isGenerating.value = false;
  }
};

const resetForm = () => {
  reportForm.value = {
    type: '',
    startDate: '',
    endDate: '',
    livestockId: 'all',
    format: 'pdf',
  };
};

const downloadReport = async (report: Report) => {
  try {
    if (report.download_url) {
      const downloadLink = document.createElement('a');
      downloadLink.href = report.download_url;
      downloadLink.click();

      // Refresh reports to update download count
      await fetchReports();
    }
  } catch (error) {
    console.error('Kesalahan mengunduh laporan:', error);
    alert('Gagal mengunduh laporan. Silakan coba lagi.');
  }
};

const deleteReport = async (reportId: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
    try {
      await axios.delete(`/reports/${reportId}`);
      await fetchReports(); // Refresh the list
    } catch (error) {
      console.error('Kesalahan menghapus laporan:', error);
      alert('Gagal menghapus laporan. Silakan coba lagi.');
    }
  }
};

const getReportTypeName = (type: string) => {
  const names: Record<ReportType, string> = {
    'livestock-summary': 'Ringkasan_Ternak',
    'feeding-report': 'Laporan_Pakan',
    'milking-report': 'Laporan_Susu',
    'weight-report': 'Laporan_Bobot',
    'health-report': 'Laporan_Kesehatan',
    'productivity-report': 'Laporan_Produktivitas',
    'financial-report': 'Laporan_Keuangan',
    'breeding-report': 'Laporan_Perkawinan',
  };
  return names[type as ReportType] || type;
};

const formatDate = (date: Date | string) => {
  if (!date) return 'T/A';
  
  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    
    // Check if the date is valid
    if (isNaN(dateObj.getTime())) {
      return 'Tanggal Tidak Valid';
    }
    
    return new Intl.DateTimeFormat('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(dateObj);
  } catch (error) {
    console.error('Kesalahan memformat tanggal:', date, error);
    return 'Tanggal Tidak Valid';
  }
};

const formatDateRange = () => {
  const start = new Date(reportForm.value.startDate);
  const end = new Date(reportForm.value.endDate);
  const startMonth = start.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
  const endMonth = end.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });

  if (startMonth === endMonth) {
    return startMonth;
  }
  return `${startMonth} - ${endMonth}`;
};

// Atur rentang tanggal default (30 hari terakhir)
const setDefaultDates = () => {
  const end = new Date();
  const start = new Date();
  start.setDate(start.getDate() - 30);

  reportForm.value.endDate = end.toISOString().split('T')[0];
  reportForm.value.startDate = start.toISOString().split('T')[0];
};

// Inisialisasi komponen
onMounted(async () => {
  setDefaultDates();
  await Promise.all([
    fetchReports(),
    fetchLivestock()
  ]);
});
</script>