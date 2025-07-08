<script setup lang="ts">
// Core Vue imports
import { ref, computed } from "vue";
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";

// Layout and Components
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { FloatingInput } from "@/components/ui/floating-input";
import { MapInput } from "@/components/ui/map-input";
import { ImageUpload } from "@/components/ui/image-upload";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import InputError from "@/components/InputError.vue";

// Icons
import {
  Mars,
  Venus,
  ArrowLeft,
  Megaphone,
  ChartNoAxesColumnIncreasing,
  ThumbsUp,
  ThumbsDown,
  MoreVertical,
  Pencil,
  Trash2,
  ImageOff,
  Scale,
  Milk,
} from "lucide-vue-next";

// Types
import type { BreadcrumbItem } from "@/types";

// Local components
import AlertBanner from "./components/AlertBanner.vue";
import LineChart from "./components/LineChart.vue";
import HealthHistoryCard from "./components/HealthHistoryCard.vue";
import FeedHistoryCard from "./components/FeedHistoryCard.vue";
import FamilyTree from "./components/FamilyTree.vue";
import OrgChart from "./components/OrgChart.vue";

// Assets
import Example2 from "@/assets/example-2.png";

// Props
const props = defineProps<{
  livestock: any;
  weightHistory: any[];
  milkingHistory: any[];
}>();

// Breadcrumb navigation
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Profil Peternakan",
    href: "/teams",
  },
];

// Form state and handlers
const form = useForm({
  name: "",
  email: "",
  location: {
    latitude: 0,
    longitude: 0,
  },
});

const isDarkMode = computed(() => document.documentElement.classList.contains('dark'));

const submit = () => { };

// Navigation functions
const show = (id: string) => router.visit(route("livestocks.show", { livestock: id }));
const back = () => window.history.back();

const deleteLivestock = () => {
  if (confirm('Are you sure you want to delete this livestock?')) {
    router.delete(route('livestocks.destroy', props.livestock.id));
  }
}

const getPhotoUrl = (path: string) => {
  if (path.startsWith('public/')) {
    return `/storage/${path.substring(7)}`;
  }
  return `/storage/${path}`;
}

// Data for health and feed history
const healthData = [
  {
    status: "Sehat",
    desc: "Kambing dalam keadaan baik dan bebas dari penyakit.",
    date: "17 Juni 2024",
  },
  {
    status: "Sakit",
    desc: "Peradangan pada kelenjar susu (Mastitis)",
    date: "16 Juni 2024",
  },
  {
    status: "Sakit",
    desc: "Infeksi bakteri pada kelenjar susu (Mastitis)",
    date: "15 Juni 2024",
  },
];

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

  // Map weight history to months
  const weightMap = new Map<string, number>();
  if (props.weightHistory && props.weightHistory.length > 0) {
    props.weightHistory.forEach((weight: any) => {
      const date = new Date(weight.date);
      const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
      weightMap.set(monthKey, parseFloat(weight.weight));
    });
  }

  // Create data points, using 0 for months without data
  last12Months.forEach(month => {
    weightDataPoints.push(weightMap.get(month.key) || 0);
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
          <Button @click="back" variant="outline" size="icon" class="h-10 w-10 shrink-0">
            <ArrowLeft class="h-5 w-5" />
          </Button>
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold tracking-tight">{{ livestock.name }}</h1>
              <Badge :variant="livestock.status.value == 1 ? 'default' : 'destructive'">
                {{ livestock.tag_id }}</Badge>
            </div>
            <div class="flex items-center gap-2">
              <p class="text-muted-foreground">{{ livestock.breed.species.name }} - {{ livestock.breed.name }}</p>
              <Mars v-if="livestock.sex == 'M'" class="text-blue-500" />
              <Venus v-else class="text-pink-500" />
            </div>
          </div>
        </div>
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon">
              <MoreVertical class="h-5 w-5" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <Link :href="route('livestocks.weighting')" class="flex items-center gap-2">
              <Scale class="h-4 w-4" />
              <span>Tambah Bobot</span>
              </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
              <Link :href="route('livestocks.milking')" class="flex items-center gap-2">
              <Milk class="h-4 w-4" />
              <span>Tambah Data Perahan</span>
              </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <Link :href="route('livestocks.edit', livestock.id)" class="flex items-center gap-2">
              <Pencil class="h-4 w-4" />
              <span>Edit</span>
              </Link>
            </DropdownMenuItem>
            <DropdownMenuItem @click="deleteLivestock" class="flex items-center gap-2 text-red-600">
              <Trash2 class="h-4 w-4" />
              <span>Delete</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Population Stats Card -->
      <Card class="border-0 bg-primary">
        <CardContent class="pt-4 flex justify-center">
          <div v-if="props.livestock.photo && props.livestock.photo.length > 0" class="relative w-full max-w-2xl">
            <Carousel class="relative w-full" :opts="{ align: 'center' }">
              <CarouselContent>
                <CarouselItem v-for="(photo, index) in props.livestock.photo" :key="index">
                  <div class="p-1">
                    <img :src="getPhotoUrl(photo)" alt="Livestock photo" class="rounded-lg object-cover w-full h-96">
                  </div>
                </CarouselItem>
              </CarouselContent>
              <CarouselPrevious v-if="props.livestock.photo.length > 1" />
              <CarouselNext v-if="props.livestock.photo.length > 1" />
            </Carousel>
          </div>
          <div v-else
            class="flex flex-col items-center justify-center w-full max-w-2xl h-96 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <ImageOff class="h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" />
            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">Tidak ada foto</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm">Foto ternak belum tersedia</p>
          </div>
        </CardContent>
      </Card>

      <!-- Grid Section -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-white font-sans">
        <!-- GOAT SECTION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Umur</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-4 rounded-b-lg text-5xl font-semibold">
            <template v-if="livestock.age_in_year > 0">
              {{ livestock.age_in_year }} <span class="text-sm font-normal">Tahun</span>
            </template>
            <template v-else>
              {{ livestock.age_in_month }} <span class="text-sm font-normal">Bulan</span>
            </template>
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
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-5xl font-semibold">
            {{ livestock.weight }} <span class="text-sm font-normal">Kg</span>
          </div>
        </div>

        <!-- MILK SECTION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Hasil Susu</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-5xl font-semibold">
            {{props.milkingHistory.reduce((sum, val) => sum + (val.total_volume || 0), 0).toFixed(1)}} <span
              class="text-sm font-normal">Liter</span>
          </div>
        </div>

        <!-- MILK SECTION -->
        <div class="rounded-lg flex flex-col border border-primary/20 dark:border-primary/80">
          <!-- Header -->
          <div class="bg-primary text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
            <ChartNoAxesColumnIncreasing class="text-white" />
            <span class="text-white">Ranking</span>
          </div>

          <!-- Big fat number -->
          <div
            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-5xl font-semibold">
            00 <span class="text-sm font-normal">Liter</span>
          </div>
        </div>
      </div>

      <!-- Graphs -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card class="border border-primary/20 dark:border-primary/80">
          <CardContent class="p-4">
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
        </Card>

        <Card class="border border-primary/20 dark:border-primary/80">
          <CardContent class="p-4">
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
                    Perkembangan bobot ternak {{ weightTrend.isIncreasing ? 'meningkat' : 'menurun' }}
                    {{ Math.abs(parseFloat(weightTrend.difference)) }}kg atau {{
                      Math.abs(parseFloat(weightTrend.percentage)) }}%
                    dari bobot sebelumnya
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
        </Card>
      </div>

      <!-- Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <HealthHistoryCard :history="healthData" />
        <FeedHistoryCard :feed="feedData" />
      </div>

      <!-- <FamilyTree :goat="goatData" /> -->

      <OrgChart />
    </div>
  </AppLayout>
</template>
