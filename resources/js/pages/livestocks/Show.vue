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
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
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
  name?: string;
  labels?: string[];
  dataPoints?: number[];
  label?: string;
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
const show = (id: string) => router.visit(route("livestocks.show", id));
const back = () => window.history.back();

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



const goatData = {
  id: '009',
  name: 'Slamet',
  breed: 'Etawa PE',
  image: '/images/009.jpg',
  mother: {
    id: '001',
    name: 'Cantik Putih',
    breed: 'Etawa PO',
    image: '/images/001.jpg',
    mother: {
      id: '021',
      name: 'Cantik Putih',
      breed: 'Etawa PO',
      image: '/images/021.jpg'
    },
    father: {
      id: '020',
      name: 'Tanduk Lengkung',
      breed: 'Etawa PE',
      image: '/images/020.jpg'
    }
  },
  father: {
    id: '002',
    name: 'Tanduk Lengkung',
    breed: 'Etawa PE',
    image: '/images/002.jpg',
    mother: {
      id: '023',
      name: 'Cantik Putih',
      breed: 'Etawa PO',
      image: '/images/023.jpg',
      mother: {
        id: '021',
        name: 'Cantik Putih',
        breed: 'Etawa PO',
        image: '/images/021.jpg'
      },
      father: {
        id: '020',
        name: 'Tanduk Lengkung',
        breed: 'Etawa PE',
        image: '/images/020.jpg'
      }
    },
    father: {
      id: '024',
      name: 'Tanduk Lengkung',
      breed: 'Etawa PE',
      image: '/images/024.jpg',
      mother: {
        id: '023',
        name: 'Cantik Putih',
        breed: 'Etawa PO',
        image: '/images/023.jpg'
      },
      father: {
        id: '024',
        name: 'Tanduk Lengkung',
        breed: 'Etawa PE',
        image: '/images/024.jpg'
      }
    }
  }
}
</script>

<template>

  <Head title="Populasi" />

  <AppLayout>
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
      <!-- Header Section -->
      <div class="flex items-start space-x-4">
        <Button variant="ghost" size="icon" @click="back">
          <ArrowLeft />
        </Button>
        <h1 class="text-3xl font-semibold">Putih</h1>
        <div class="flex flex-col">
          <Badge class="bg-primary text-white rounded-full">ID-007</Badge>
          <p class="text-sm">Etawa PE</p>
        </div>
      </div>

      <!-- Population Stats Card -->
      <Card class="border-0 bg-primary">
        <CardContent class="pt-4">
          <p class="text-white font-sans mb-4">Populasi ternak jantan dan betina di peternakan Anda:</p>
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-white font-sans">
            <!-- Goat Section -->
            <div class="rounded-2xl flex flex-col">
              <div
                class="bg-teal-500 dark:bg-teal-200 text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
                <Mars />
                <span>Kambing</span>
              </div>
              <div
                class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-4 rounded-b-lg text-5xl font-semibold">
                00 <span class="text-sm font-normal">Ekor</span>
              </div>
            </div>

            <!-- Milk Section -->
            <div class="rounded-2xl flex flex-col">
              <div
                class="bg-cyan-500 dark:bg-cyan-200 text-white dark:text-black rounded-t-lg px-4 py-2 flex items-center gap-2">
                <Venus />
                <span>Susu</span>
              </div>
              <div
                class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 rounded-b-lg text-5xl font-semibold">
                00 <span class="text-sm font-normal">Liter</span>
              </div>
            </div>
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
            00 <span class="text-sm font-normal">Ekor</span>
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
            00 <span class="text-sm font-normal">Liter</span>
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
            00 <span class="text-sm font-normal">Liter</span>
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
            <Alert variant="success" class="mb-2">
              <ThumbsUp class="h-4 w-4" />
              <AlertTitle>Produktivitas Susu</AlertTitle>
              <AlertDescription class="text-sm">Hasil susu ternakmu hari ini meningkat 100 ml atau 5%</AlertDescription>
            </Alert>
            <LineChart :labels="['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']"
              :dataPoints="[1, 1.2, 1.1, 3.5, 4, 5, 6, 7, 3, 8, 10, 11]" label="Produksi Susu (liter)"
              :isDark="isDarkMode" />
          </CardContent>
        </Card>

        <Card class="border border-primary/20 dark:border-primary/80">
          <CardContent class="p-4">
            <Alert variant="danger" class="mb-2">
              <ThumbsDown class="h-4 w-4" />
              <AlertTitle>Produktivitas Bobot</AlertTitle>
              <AlertDescription class="text-sm">Perkembangan bobot ternakmu hari ini menurun 4kg atau -10% dari bobot
                sebelumnya</AlertDescription>
            </Alert>
            <LineChart :labels="['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']"
              :dataPoints="[10, 30, 40, 60, 70, 80, 80, 90, 100, 80, 110, 130]" label="Bobot Ternak (kg)"
              :isDark="isDarkMode" />
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
