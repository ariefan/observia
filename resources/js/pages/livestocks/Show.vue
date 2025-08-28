<script setup lang="ts">
// Core Vue imports
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { usePhotoUrl } from '@/composables/usePhotoUrl';

// Layout and Components
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card"
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Label } from "@/components/ui/label";

// Icons
import {
  Mars,
  Venus,
  ArrowLeft,
  ChartNoAxesColumnIncreasing,
  ThumbsUp,
  ThumbsDown,
  MoreVertical,
  Pencil,
  Trash2,
  ImageOff,
  TreeDeciduous,
  Award,
  Skull,
} from "lucide-vue-next";

// Types

// Local components
import LineChart from "./components/LineChart.vue";
import HealthHistoryCard from "./components/HealthHistoryCard.vue";
import FeedHistoryCard from "./components/FeedHistoryCard.vue";
import PedigreeCard from "./components/PedigreeCard.vue";
import ImagePreview from "@/components/ImagePreview.vue";

// Props
const props = defineProps<{
  livestock: any;
  weightHistory: any[];
  milkingHistory: any[];
  lactationDays: number;
  rank?: number;
  totalRanked?: number;
  feedingHistory?: any[];
  pedigreeData?: any[];
  latestEnding?: any;
  healthRecords?: any[];
}>();

// Breadcrumb navigation


const isDarkMode = computed(() => document.documentElement.classList.contains('dark'));

// Composables
const { getPhotoUrl } = usePhotoUrl();

const deleteLivestock = () => {
  if (confirm('Are you sure you want to delete this livestock?')) {
    router.delete(route('livestocks.destroy', props.livestock.id));
  }
}

const downloadStudbook = () => {
  // Open studbook PDF in new tab
  window.open(route('livestocks.studbook', props.livestock.id), '_blank');
}

// Helper function to get status badge variant and label
const getEndingStatusInfo = () => {
  if (!props.latestEnding) return null;
  
  const statusMap = {
    'sold': { label: 'Dijual', variant: 'default' },
    'gifted': { label: 'Hibah', variant: 'secondary' },
    'loaned': { label: 'Dipinjam', variant: 'outline' },
    'died': { label: 'Mati', variant: 'destructive' },
    'slaughtered': { label: 'Dipotong', variant: 'destructive' },
  };
  
  return statusMap[props.latestEnding.ending_status] || { label: props.latestEnding.ending_status, variant: 'secondary' };
};

// Data for feed history

const feedData = [
  {
    name: "Ransum 1C",
    date: "17 Juni 2024",
    qty: "1 kg",
    avatar: "https://randomuser.me/api/portraits/thumb/men/1.jpg",
  },
  {
    name: "Ransum 1B",
    date: "16 Juni 2024",
    qty: "5 kg",
    avatar: "https://randomuser.me/api/portraits/thumb/women/2.jpg",
  },
  {
    name: "Ransum 2A",
    date: "15 Juni 2024",
    qty: "2 kg",
    avatar: "https://randomuser.me/api/portraits/thumb/men/3.jpg",
  },
];

// Process weight history data for the chart
const processWeightData = () => {
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const last12Months: Array<{ key: string, label: string, year: number }> = [];
  const weightDataPoints: number[] = [];

  // Generate last 12 months
  for (let i = 11; i >= 0; i--) {
    const date = new Date();
    date.setMonth(date.getMonth() - i);
    const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    last12Months.push({
      key: monthKey,
      label: monthNames[date.getMonth()],
      year: date.getFullYear()
    });
  }

  // Map weight history to months using average_weight and month
  const weightMap = new Map<string, number>();
  if (props.weightHistory && props.weightHistory.length > 0) {
    props.weightHistory.forEach((weight: any) => {
      weightMap.set(weight.month, parseFloat(weight.average_weight));
    });
  }

  // Create data points, using last value for months without data
  let lastValue = 0;
  last12Months.forEach(month => {
    if (weightMap.has(month.key)) {
      lastValue = weightMap.get(month.key) || 0;
    }
    weightDataPoints.push(lastValue);
  });

  return {
    labels: last12Months.map(m => m.label),
    dataPoints: weightDataPoints
  };
};

const weightChartData = processWeightData();

// Process milking history data for the chart
const processMilkingData = () => {
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const last12Months: Array<{ key: string, label: string, year: number }> = [];
  const milkingDataPoints: number[] = [];

  // Generate last 12 months
  for (let i = 11; i >= 0; i--) {
    const date = new Date();
    date.setMonth(date.getMonth() - i);
    const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    last12Months.push({
      key: monthKey,
      label: monthNames[date.getMonth()],
      year: date.getFullYear()
    });
  }

  // Map milking history to months
  const milkingMap = new Map<string, number>();
  if (props.milkingHistory && props.milkingHistory.length > 0) {
    props.milkingHistory.forEach((milking: any) => {
      const date = new Date(milking.date);
      const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
      milkingMap.set(monthKey, parseFloat(milking.average_volume));
    });
  }

  // Create data points, using 0 for months without data
  last12Months.forEach(month => {
    milkingDataPoints.push(milkingMap.get(month.key) || 0);
  });

  return {
    labels: last12Months.map(m => m.label),
    dataPoints: milkingDataPoints
  };
};

const milkingChartData = processMilkingData();

// Dialog states for monthly details
const showMilkingDialog = ref(false);
const showWeightDialog = ref(false);
const selectedMonth = ref(new Date().getMonth());
const selectedYear = ref(new Date().getFullYear());

// Month names
const months = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// Get month and year display name
const getMonthYearName = (month, year) => {
  return `${months[month]} ${year}`;
};

// Get available years from milking and weight history
const availableYears = computed(() => {
  const years = new Set();
  
  if (props.milkingHistory && props.milkingHistory.length > 0) {
    props.milkingHistory.forEach(milking => {
      const date = new Date(milking.date);
      years.add(date.getFullYear());
    });
  }
  
  if (props.weightHistory && props.weightHistory.length > 0) {
    props.weightHistory.forEach(weight => {
      const [year] = weight.month.split('-');
      years.add(parseInt(year));
    });
  }
  
  const yearArray = Array.from(years).sort((a, b) => b - a); // Latest year first
  return yearArray.length > 0 ? yearArray : [new Date().getFullYear()];
});

// Open dialog functions
const openMilkingDialog = () => {
  selectedMonth.value = new Date().getMonth();
  selectedYear.value = new Date().getFullYear();
  showMilkingDialog.value = true;
};

const openWeightDialog = () => {
  selectedMonth.value = new Date().getMonth();
  selectedYear.value = new Date().getFullYear();
  showWeightDialog.value = true;
};

// Get filtered milking data based on selected month/year
const filteredMilkingData = computed(() => {
  if (!props.milkingHistory || props.milkingHistory.length === 0) {
    return [];
  }

  return props.milkingHistory.filter(milking => {
    const milkingDate = new Date(milking.date);
    return milkingDate.getMonth() === selectedMonth.value && milkingDate.getFullYear() === selectedYear.value;
  }).sort((a, b) => new Date(b.date) - new Date(a.date));
});

// Get filtered weight data based on selected month/year
const filteredWeightData = computed(() => {
  if (!props.weightHistory || props.weightHistory.length === 0) {
    return [];
  }

  const monthKey = `${selectedYear.value}-${String(selectedMonth.value + 1).padStart(2, '0')}`;
  return props.weightHistory.filter(weight => weight.month === monthKey);
});

// Format date for display
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const options = {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Jakarta'
  };
  return date.toLocaleDateString('id-ID', options);
};

// Calculate weight trend
const weightTrend = computed(() => {
  const dataPoints = weightChartData.dataPoints.filter(point => point > 0);

  if (dataPoints.length === 0) {
    return {
      difference: '0',
      percentage: '0',
      isIncreasing: false,
      hasData: false
    };
  }

  if (dataPoints.length === 1) {
    return {
      difference: '0',
      percentage: '0',
      isIncreasing: false,
      hasData: true,
      singleDataPoint: true
    };
  }

  const lastWeight = dataPoints[dataPoints.length - 1];
  const previousWeight = dataPoints[dataPoints.length - 2];
  const difference = lastWeight - previousWeight;
  const percentage = ((difference / previousWeight) * 100).toFixed(1);

  return {
    difference: difference.toFixed(1),
    percentage: percentage,
    isIncreasing: difference > 0,
    hasData: true
  };
});

// Calculate milking trend
const milkingTrend = computed(() => {
  const dataPoints = milkingChartData.dataPoints.filter(point => point > 0);

  if (dataPoints.length === 0) {
    return {
      difference: '0',
      percentage: '0',
      isIncreasing: false,
      hasData: false
    };
  }

  if (dataPoints.length === 1) {
    return {
      difference: '0',
      percentage: '0',
      isIncreasing: false,
      hasData: true,
      singleDataPoint: true
    };
  }

  const lastMilk = dataPoints[dataPoints.length - 1];
  const previousMilk = dataPoints[dataPoints.length - 2];
  const difference = lastMilk - previousMilk;
  const percentage = ((difference / previousMilk) * 100).toFixed(1);

  return {
    difference: difference.toFixed(1),
    percentage: percentage,
    isIncreasing: difference > 0,
    hasData: true
  };
});
</script>

<template>

  <Head title="Populasi" />

  <AppLayout>
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
      <!-- Header Section -->
      <div class="flex items-start justify-between space-x-4">
        <div class="flex items-start space-x-4">
          <Button @click="router.get(route('livestocks.index'))" variant="outline" size="icon"
            class="h-10 w-10 shrink-0">
            <ArrowLeft class="h-5 w-5" />
          </Button>
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold tracking-tight">{{ livestock.name }}</h1>
              <Badge :variant="livestock.status.value == 1 ? 'default' : 'destructive'">
                {{ livestock.tag_id }}</Badge>
              <Badge v-if="latestEnding" :variant="getEndingStatusInfo()?.variant as any" class="ml-2">
                {{ getEndingStatusInfo()?.label }}
              </Badge>
            </div>
            <div class="flex items-center gap-2">
              <p class="text-muted-foreground">{{ livestock.breed.species.name }} - {{ livestock.breed.name }}</p>
              <Mars v-if="livestock.sex == 'M'" class="text-blue-500" />
              <Venus v-else class="text-pink-500" />
              <ImagePreview v-if="livestock.photo && livestock.photo.length > 0" :photos="livestock.photo"
                trigger-class="h-6 w-6 text-muted-foreground hover:text-primary" />
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <Badge v-if="livestock.herd" class="text-right text-base">
            <p class="font-medium">{{ livestock.herd.name }}</p>
          </Badge>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="icon">
                <MoreVertical class="h-5 w-5" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem as-child>
                <Link :href="route('livestocks.edit', livestock.id)" class="flex items-center gap-2">
                <Pencil class="h-4 w-4" />
                <span>Edit data ternak</span>
                </Link>
              </DropdownMenuItem>
              <DropdownMenuItem @click="downloadStudbook" class="flex items-center gap-2 cursor-pointer">
                <TreeDeciduous class="h-4 w-4" />
                <span>Studbook</span>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <div class="flex items-center gap-2">
                  <Award class="h-4 w-4" />
                  <span>Sertifikat ternak</span>
                </div>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <Link :href="route('livestock-endings.create', { livestock_id: livestock.id })" class="flex items-center gap-2">
                  <Skull class="h-4 w-4" />
                  <span>Data end</span>
                </Link>
              </DropdownMenuItem>
              <DropdownMenuItem @click="deleteLivestock" class="flex items-center gap-2 text-red-600">
                <Trash2 class="h-4 w-4" />
                <span>Hapus</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Population Stats Card -->
      <Card class="border-0 bg-primary">
        <CardContent class="p-2 pt-4 flex justify-center">
          <div v-if="props.livestock.photo && props.livestock.photo.length > 0" class="relative w-full max-w-7xl">
            <Carousel class="relative w-full mx-auto" :opts="{
              align: 'start',
            }">
              <CarouselContent>
                <CarouselItem v-for="(photo, index) in props.livestock.photo" :key="index" class="lg:basis-1/2">
                  <div class="p-1">
                    <div class="p-1">
                      <img :src="getPhotoUrl(photo)" alt="Livestock photo" class="rounded-lg object-cover w-full h-96">
                    </div>
                  </div>
                </CarouselItem>
              </CarouselContent>
              <CarouselPrevious class="ml-10" v-if="props.livestock.photo.length > 1" />
              <CarouselNext class="mr-10" v-if="props.livestock.photo.length > 1" />
            </Carousel>
          </div>
          <div v-else
            class="flex flex-col items-center justify-center w-full max-w-7xl h-96 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <ImageOff class="h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" />
            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">Tidak ada foto</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm">Foto ternak belum tersedia</p>
            <Button @click="router.get(route('livestocks.edit', livestock.id))" variant="outline" class="mt-4">
              Tambah Foto
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Grid Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 text-white font-sans">
        <!-- GOAT SECTION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Umur</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-4 rounded-b-lg text-3xl font-semibold">
            {{ livestock.age_in_year }} <span class="text-xl font-normal">th</span>
            {{ livestock.age_in_month % 12 }} <span class="text-xl font-normal">bl</span>
          </div>
        </div>

        <!-- MILK SECTION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Bobot</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-3xl font-semibold">
            {{ livestock.weight }} <span class="text-sm font-normal">Kg</span>
          </div>
        </div>

        <!-- MILK SECTION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white text-sm">Rata-rata Produksi</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-3xl font-semibold">
            <template v-if="lactationDays > 0">
              {{(props.milkingHistory.reduce((sum, val) => sum + (val.total_volume || 0), 0) /
                lactationDays).toFixed(1)}}
              <span class="text-sm font-normal">Liter/hari</span>
            </template>
            <template v-else>
              -
            </template>
          </div>
        </div>

        <!-- Ranking -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Ranking</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-3xl font-semibold">
            <template v-if="rank && livestock.sex !== 'M'">
              {{ rank }}<span class="text-base font-normal">/{{ totalRanked }}</span>
            </template>
            <template v-else>
              -
            </template>
          </div>
        </div>

        <!-- MILK SECTION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Produksi Susu</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-3xl font-semibold">
            <template v-if="livestock.sex !== 'M'">
              {{props.milkingHistory.reduce((sum, val) => sum + (val.total_volume || 0), 0).toFixed(1)}} <span
                class="text-sm font-normal">
                Liter
              </span>
            </template>
            <template v-else>
              -
            </template>
          </div>
        </div>

        <!-- LACTATION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Hari Laktasi</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-3xl font-semibold">
            <template v-if="livestock.sex !== 'M'">
              {{ lactationDays }} <span class="text-sm font-normal">
                hari
              </span>
            </template>
            <template v-else>
              -
            </template>
          </div>
        </div>
      </div>

      <!-- Graphs -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card class="border border-primary/20 dark:border-primary/80 flex flex-col h-full">
          <CardContent class="p-4 flex-1">
            <Alert
              :variant="milkingTrend?.hasData && !milkingTrend.singleDataPoint ? (milkingTrend.isIncreasing ? 'success' : 'destructive') : 'default'"
              class="mb-2">
              <ThumbsUp v-if="milkingTrend?.hasData && !milkingTrend.singleDataPoint && milkingTrend.isIncreasing"
                class="h-4 w-4" />
              <ThumbsDown
                v-else-if="milkingTrend?.hasData && !milkingTrend.singleDataPoint && !milkingTrend.isIncreasing"
                class="h-4 w-4" />
              <AlertTitle>Rata-rata Produktivitas Susu</AlertTitle>
              <AlertDescription class="text-sm">
                <template v-if="milkingTrend?.hasData">
                  <template v-if="milkingTrend.singleDataPoint">
                    Data rata-rata produksi susu tersedia untuk bulan ini. Tambahkan data perahan bulan berikutnya untuk
                    melihat tren
                    perbandingan.
                  </template>
                  <template v-else>
                    Rata-rata produksi susu {{ milkingTrend.isIncreasing ? 'meningkat' : 'menurun' }}
                    {{ Math.abs(parseFloat(milkingTrend.difference)) }} liter/hari atau {{
                      Math.abs(parseFloat(milkingTrend.percentage)) }}%
                    dari bulan sebelumnya
                  </template>
                </template>
                <template v-else>
                  Belum ada data produksi susu yang tercatat. Mulai tambahkan data perahan untuk melihat tren rata-rata
                  produksi.
                </template>
              </AlertDescription>
            </Alert>
            <LineChart :labels="milkingChartData.labels" :dataPoints="milkingChartData.dataPoints"
              label="Rata-rata Produksi Susu (liter/hari)" :isDark="isDarkMode" xAxisLabel="Bulan"
              yAxisLabel="Rata-rata Produksi Susu (liter/hari)" />
          </CardContent>
          <div class="p-0 mt-auto">
            <Button @click="openMilkingDialog" variant="ghost"
              class="w-full justify-center rounded-none text-primary hover:bg-primary/10 py-3" style="box-shadow: none;">
              <span class="font-semibold underline">Lihat bulan ini</span>
            </Button>
          </div>
        </Card>

        <Card class="border border-primary/20 dark:border-primary/80 flex flex-col h-full">
          <CardContent class="p-4 flex-1">
            <Alert
              :variant="weightTrend?.hasData && !weightTrend.singleDataPoint ? (weightTrend.isIncreasing ? 'success' : 'destructive') : 'default'"
              class="mb-2">
              <ThumbsUp v-if="weightTrend?.hasData && !weightTrend.singleDataPoint && weightTrend.isIncreasing"
                class="h-4 w-4" />
              <ThumbsDown v-else-if="weightTrend?.hasData && !weightTrend.singleDataPoint && !weightTrend.isIncreasing"
                class="h-4 w-4" />
              <AlertTitle>Produktivitas Bobot</AlertTitle>
              <AlertDescription class="text-sm">
                <template v-if="weightTrend?.hasData">
                  <template v-if="weightTrend.singleDataPoint">
                    Data bobot ternak tersedia untuk bulan ini. Tambahkan data bobot bulan berikutnya untuk melihat tren
                    perkembangan.
                  </template>
                  <template v-else>
                    Perkembangan bobot ternakmu bulan ini {{ weightTrend.isIncreasing ? 'meningkat' : 'menurun' }}
                    {{ Math.abs(parseFloat(weightTrend.difference)) }}kg atau {{
                      Math.abs(parseFloat(weightTrend.percentage)) }}%
                    dari bobot bulan sebelumnya
                  </template>
                </template>
                <template v-else>
                  Belum ada data bobot ternak yang tercatat. Mulai tambahkan data bobot untuk melihat tren perkembangan.
                </template>
              </AlertDescription>
            </Alert>
            <LineChart :labels="weightChartData.labels" :dataPoints="weightChartData.dataPoints"
              label="Bobot Ternak (kg)" :isDark="isDarkMode" xAxisLabel="Bulan" yAxisLabel="Bobot Ternak (kg)" />
          </CardContent>
          <div class="p-0 mt-auto">
            <Button @click="openWeightDialog" variant="ghost"
              class="w-full justify-center rounded-none text-primary hover:bg-primary/10 py-3" style="box-shadow: none;">
              <span class="font-semibold underline">Lihat bulan ini</span>
            </Button>
          </div>
        </Card>
      </div>

      <!-- Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <HealthHistoryCard :history="healthRecords" />
        <FeedHistoryCard :feed="feedData" :feedingHistory="feedingHistory" />
      </div>

      <!-- <FamilyTree :goat="goatData" /> -->

      <!-- Pedigree Section -->
      <PedigreeCard :pedigreeData="pedigreeData" />
    </div>
  </AppLayout>

  <!-- Monthly Milking Dialog -->
  <Dialog v-model:open="showMilkingDialog">
    <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Data Produksi Susu - {{ getMonthYearName(selectedMonth, selectedYear) }}</DialogTitle>
        <DialogDescription>
          Seluruh data produksi susu pada bulan {{ getMonthYearName(selectedMonth, selectedYear) }}
        </DialogDescription>
      </DialogHeader>

      <!-- Month/Year Filter -->
      <div class="flex gap-4 mb-4">
        <div class="flex-1">
          <Label for="milking-month-select" class="text-sm font-medium">Bulan</Label>
          <Select v-model="selectedMonth">
            <SelectTrigger id="milking-month-select">
              <SelectValue placeholder="Pilih bulan" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="(month, index) in months" :key="index" :value="index">
                {{ month }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="flex-1">
          <Label for="milking-year-select" class="text-sm font-medium">Tahun</Label>
          <Select v-model="selectedYear">
            <SelectTrigger id="milking-year-select">
              <SelectValue placeholder="Pilih tahun" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div v-if="filteredMilkingData && filteredMilkingData.length > 0" class="space-y-4">
        <div v-for="milking in filteredMilkingData" :key="milking.id" class="border-b pb-3 last:border-b-0">
          <div class="flex justify-between items-center mb-2">
            <p class="font-semibold text-sm">{{ formatDate(milking.date) }}</p>
            <Badge class="text-xs">
              {{ parseFloat(milking.total_volume || 0).toFixed(1) }} liter
            </Badge>
          </div>
          <div class="ml-4 space-y-1">
            <div class="text-sm text-muted-foreground">
              <span class="font-medium">Volume rata-rata:</span> {{ parseFloat(milking.average_volume || 0).toFixed(1) }} liter/hari
            </div>
            <div v-if="milking.notes" class="text-xs text-muted-foreground italic">
              "{{ milking.notes }}"
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-muted-foreground">
        <p class="text-sm">Belum ada data produksi susu pada {{ getMonthYearName(selectedMonth, selectedYear) }}</p>
      </div>

      <DialogFooter>
        <Button @click="showMilkingDialog = false" variant="secondary">Tutup</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <!-- Monthly Weight Dialog -->
  <Dialog v-model:open="showWeightDialog">
    <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Data Bobot Ternak - {{ getMonthYearName(selectedMonth, selectedYear) }}</DialogTitle>
        <DialogDescription>
          Seluruh data bobot ternak pada bulan {{ getMonthYearName(selectedMonth, selectedYear) }}
        </DialogDescription>
      </DialogHeader>

      <!-- Month/Year Filter -->
      <div class="flex gap-4 mb-4">
        <div class="flex-1">
          <Label for="weight-month-select" class="text-sm font-medium">Bulan</Label>
          <Select v-model="selectedMonth">
            <SelectTrigger id="weight-month-select">
              <SelectValue placeholder="Pilih bulan" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="(month, index) in months" :key="index" :value="index">
                {{ month }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="flex-1">
          <Label for="weight-year-select" class="text-sm font-medium">Tahun</Label>
          <Select v-model="selectedYear">
            <SelectTrigger id="weight-year-select">
              <SelectValue placeholder="Pilih tahun" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div v-if="filteredWeightData && filteredWeightData.length > 0" class="space-y-4">
        <div v-for="weight in filteredWeightData" :key="weight.month" class="border-b pb-3 last:border-b-0">
          <div class="flex justify-between items-center mb-2">
            <p class="font-semibold text-sm">{{ getMonthYearName(selectedMonth, selectedYear) }}</p>
            <Badge class="text-xs">
              {{ parseFloat(weight.average_weight || 0).toFixed(1) }} kg
            </Badge>
          </div>
          <div class="ml-4 space-y-1">
            <div class="text-sm text-muted-foreground">
              <span class="font-medium">Bobot rata-rata bulan:</span> {{ parseFloat(weight.average_weight || 0).toFixed(1) }} kg
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-muted-foreground">
        <p class="text-sm">Belum ada data bobot ternak pada {{ getMonthYearName(selectedMonth, selectedYear) }}</p>
      </div>

      <DialogFooter>
        <Button @click="showWeightDialog = false" variant="secondary">Tutup</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
