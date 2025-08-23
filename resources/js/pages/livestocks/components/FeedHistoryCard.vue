<template>
  <Card class="border border-primary/20 dark:border-primary/80 flex flex-col h-full">
    <CardContent class="p-4 flex-1">
      <h3 class="font-semibold text-lg mb-4">Riwayat Pakan</h3>
      <ul v-if="groupedFeedings && Object.keys(groupedFeedings).length > 0" class="space-y-3">
        <li v-for="(dateGroup, date) in groupedFeedings" :key="date" class="pb-3">
          <div class="flex justify-between items-start">
            <div class="flex flex-wrap gap-2">
              <Badge v-for="session in ['morning', 'afternoon', 'evening', 'night']" :key="session" variant="secondary"
                :class="dateGroup.sessions[session] ? '' : 'opacity-50'" class="text-xs">
                {{ translateSession(session) }}
                <span v-if="dateGroup.sessions[session]" class="ml-1 hidden">
                  <template v-if="dateGroup.livestockCount && dateGroup.livestockCount > 1">
                    ({{ (dateGroup.sessions[session].quantityPerLivestock).toFixed(2) }}kg/ekor)
                  </template>
                  <template v-else>
                    ({{ dateGroup.sessions[session].quantity }}kg)
                  </template>
                </span>
              </Badge>
            </div>
            <div class="text-right">
              <div class="text-sm font-semibold">{{ formatDate(date.toString()) }}</div>
              <div class="text-sm font-semibold text-muted-foreground">
                <Badge class="text-xs">
                  <template v-if="dateGroup.livestockCount && dateGroup.livestockCount > 1">
                    {{ dateGroup.totalQuantityPerLivestock.toFixed(2) }} kg/ekor
                  </template>
                  <template v-else>
                    {{ dateGroup.totalQuantity.toFixed(2) }} kg
                  </template>
                </Badge>
              </div>
            </div>
          </div>
        </li>
      </ul>

      <ul v-else-if="feedingHistory && feedingHistory.length > 0" class="space-y-2">
        <li v-for="feeding in feedingHistory" :key="`${feeding.id}-${feeding.date}-${feeding.time}`"
          class="flex justify-between items-start">
          <div>
            <Badge variant="default" class="rounded-full mb-1">{{ feeding.ration?.name || 'Ransum tidak diketahui' }}
            </Badge>
            <p class="text-sm mt-1">
              {{ feeding.time }} • {{ translateSession(feeding.session) }}
            </p>
            <div v-if="feeding.ration?.rationItems && feeding.ration.rationItems.length > 0"
              class="text-xs text-muted-foreground mt-1">
              Komposisi: {{feeding.ration.rationItems.map(item => `${item.feed}
              (${parseFloat(item.quantity.toString()).toFixed(2)}kg)`).join(', ')}}
            </div>
            <p v-if="feeding.notes" class="text-xs text-muted-foreground mt-1 italic">
              "{{ feeding.notes }}"
            </p>
          </div>
          <div class="text-right">
            <div class="text-sm mb-1">{{ formatDate(feeding.date) }}</div>
            <Badge :variant="feeding.leftover ? 'secondary' : 'secondary'" class="text-xs">
              {{
                feeding.leftover
                  ? ((parseFloat(feeding.quantity.toString()) - parseFloat((feeding.leftover.leftover_quantity || 0).toString())) /
                    (feeding.livestock_count || 1)).toFixed(2)
                  : (parseFloat(feeding.quantity.toString()) / (feeding.livestock_count || 1)).toFixed(2)
              }}kg/ekor
            </Badge>
          </div>
        </li>
      </ul>

      <ul v-else-if="feed && feed.length > 0" class="space-y-2">
        <li v-for="item in feed" :key="item.date" class="flex justify-between items-start">
          <div>
            <Badge variant="default" class="rounded-full mb-1">{{ item.name }}</Badge>
            <p class="text-sm mt-1">
              <template v-if="item.livestock_count">
                {{ (parseFloat(item.qty.toString()) / (item.livestock_count || 1)).toFixed(2) }} kg/ekor
              </template>
              <template v-else>
                {{ item.qty }}
              </template>
            </p>
          </div>
          <span class="text-sm">{{ item.date }}</span>
        </li>
      </ul>

      <div v-else class="text-center py-8 text-muted-foreground">
        <p class="text-sm">Belum ada riwayat pakan</p>
        <p class="text-xs">Data pemberian pakan akan muncul di sini</p>
      </div>

    </CardContent>

    <CardFooter v-if="feedingHistory && feedingHistory.length > 0" class="p-0 mt-auto">
      <Button @click="openMonthlyDialog" variant="ghost"
        class="w-full justify-center rounded-none text-primary hover:bg-primary/10 py-3" style="box-shadow: none;">
        <span class="font-semibold underline">Lihat bulan ini</span>
      </Button>
    </CardFooter>
  </Card>

  <!-- Monthly Feed Dialog -->
  <Dialog v-model:open="showMonthlyDialog">
    <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Riwayat Pakan - {{ getMonthYearName(selectedMonth, selectedYear) }}</DialogTitle>
        <DialogDescription>
          Seluruh pemberian pakan pada bulan {{ getMonthYearName(selectedMonth, selectedYear) }}
        </DialogDescription>
      </DialogHeader>

      <!-- Month/Year Filter -->
      <div class="flex gap-4 mb-4">
        <div class="flex-1">
          <Label for="month-select" class="text-sm font-medium">Bulan</Label>
          <Select v-model="selectedMonth">
            <SelectTrigger id="month-select">
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
          <Label for="year-select" class="text-sm font-medium">Tahun</Label>
          <Select v-model="selectedYear">
            <SelectTrigger id="year-select">
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

      <div v-if="filteredFeedings && filteredFeedings.length > 0" class="space-y-4">
        <div v-for="dayData in filteredFeedings" :key="dayData.date" class="last:border-b-0">
          <div class="flex justify-between items-center">
            <p class="font-semibold text-sm">{{ formatDateLong(dayData.date || '') }}</p>
            <Badge class="text-xs">
              <template v-if="dayData.livestockCount && dayData.livestockCount > 1">
                {{ dayData.totalQuantityPerLivestock.toFixed(2) }} kg/ekor
              </template>
              <template v-else>
                {{ dayData.totalQuantity.toFixed(2) }} kg
              </template>
            </Badge>
          </div>

          <div class="space-y-1 ml-4">
            <div v-for="session in ['morning', 'afternoon', 'evening', 'night']" :key="session">
              <div v-if="dayData.sessions[session]" class="space-y-1">
                <div v-for="feeding in dayData.sessions[session].feedings" :key="feeding.id"
                  class="flex justify-between items-center text-sm">
                  <div class="flex space-x-4">
                    <span class="w-16">{{ translateSession(session) }}</span>
                    <span class="font-medium">{{ feeding.ration?.name || 'Ransum tidak diketahui' }}</span>
                  </div>
                  <span class="font-semibold">{{
                    feeding.leftover
                      ? ((parseFloat(feeding.quantity.toString()) - parseFloat((feeding.leftover.leftover_quantity || 0).toString())) /
                        (feeding.livestock_count || 1)).toFixed(2)
                      : (parseFloat(feeding.quantity.toString()) / (feeding.livestock_count || 1)).toFixed(2)
                    }} kg/ekor</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Show if no feeds for the day -->
          <div
            v-if="!dayData.sessions.morning && !dayData.sessions.afternoon && !dayData.sessions.evening && !dayData.sessions.night"
            class="ml-4 text-sm text-muted-foreground italic">
            Tidak ada pemberian pakan
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-muted-foreground">
        <p class="text-sm">Belum ada riwayat pakan pada {{ getMonthYearName(selectedMonth, selectedYear) }}</p>
      </div>

      <DialogFooter>
        <Button @click="showMonthlyDialog = false" variant="secondary">Tutup</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Card, CardContent, CardFooter } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Label } from "@/components/ui/label";

// Type definitions
interface Ration {
  id: number;
  name: string;
  rationItems?: RationItem[];
}

interface RationItem {
  feed: string;
  quantity: string | number;
}

interface Leftover {
  leftover_quantity: string | number;
}

interface Feeding {
  id: number;
  date: string;
  time?: string;
  session: string;
  quantity: string | number;
  livestock_count?: number;
  ration?: Ration;
  leftover?: Leftover;
  notes?: string;
}

interface FeedItem {
  date: string;
  name: string;
  qty: string | number;
  livestock_count?: number;
}

interface SessionData {
  quantity: number;
  quantityPerLivestock: number;
  feedings: Feeding[];
}

interface GroupedSession {
  [sessionName: string]: SessionData;
}

interface RationData {
  name: string;
  totalQuantity: number;
  totalQuantityPerLivestock: number;
}

interface GroupedFeeding {
  date?: string;
  totalQuantity: number;
  totalQuantityPerLivestock: number;
  sessions: GroupedSession;
  rations?: RationData[] | { [key: string]: RationData };
  livestockCount: number;
}

interface GroupedFeedings {
  [date: string]: GroupedFeeding;
}

const props = defineProps<{
  feed?: FeedItem[];
  feedingHistory?: Feeding[];
}>();

// Group feeding history by date
const groupedFeedings = computed((): GroupedFeedings => {
  if (!props.feedingHistory || props.feedingHistory.length === 0) {
    return {};
  }

  const grouped: GroupedFeedings = {};

  props.feedingHistory.forEach((feeding: Feeding) => {
    const date = feeding.date;

    if (!grouped[date]) {
      grouped[date] = {
        totalQuantity: 0,
        totalQuantityPerLivestock: 0,
        sessions: {},
        rations: {} as { [key: string]: RationData },
        livestockCount: feeding.livestock_count || 1
      };
    }

    // Add to total quantity
    const feedQuantity = parseFloat(feeding.quantity.toString());
    const leftoverQuantity = feeding.leftover ? parseFloat((feeding.leftover.leftover_quantity || 0).toString()) : 0;
    const consumedQuantity = feedQuantity - leftoverQuantity;

    grouped[date].totalQuantity += feedQuantity;
    grouped[date].totalQuantityPerLivestock += consumedQuantity / (feeding.livestock_count || 1);

    // Group by session
    if (!grouped[date].sessions[feeding.session]) {
      grouped[date].sessions[feeding.session] = {
        quantity: 0,
        quantityPerLivestock: 0,
        feedings: []
      };
    }
    grouped[date].sessions[feeding.session].quantity += feedQuantity;
    grouped[date].sessions[feeding.session].quantityPerLivestock += consumedQuantity / (feeding.livestock_count || 1);
    grouped[date].sessions[feeding.session].feedings.push(feeding);

    // Group by ration
    const rationName = feeding.ration?.name || 'Ransum tidak diketahui';
    const dateRations = grouped[date].rations as { [key: string]: RationData };
    if (!dateRations) {
      grouped[date].rations = {} as { [key: string]: RationData };
    }
    if (!dateRations[rationName]) {
      dateRations[rationName] = {
        name: rationName,
        totalQuantity: 0,
        totalQuantityPerLivestock: 0
      };
    }
    dateRations[rationName].totalQuantity += feedQuantity;
    dateRations[rationName].totalQuantityPerLivestock += consumedQuantity / (feeding.livestock_count || 1);
  });

  // Convert rations object to array for easier templating
  Object.keys(grouped).forEach((date: string) => {
    const dateRations = grouped[date].rations as { [key: string]: RationData };
    if (dateRations) {
      grouped[date].rations = Object.values(dateRations);
    }
  });

  return grouped;
});

const formatDate = (dateStr: string): string => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const options: Intl.DateTimeFormatOptions = {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Jakarta'
  };
  return date.toLocaleDateString('id-ID', options);
};

// Dialog state
const showMonthlyDialog = ref(false);
const selectedMonth = ref(new Date().getMonth());
const selectedYear = ref(new Date().getFullYear());

const openMonthlyDialog = () => {
  selectedMonth.value = new Date().getMonth();
  selectedYear.value = new Date().getFullYear();
  showMonthlyDialog.value = true;
};

// Month names
const months = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// Get month and year display name
const getMonthYearName = (month: number, year: number): string => {
  return `${months[month]} ${year}`;
};

// Get available years from feeding history
const availableYears = computed((): number[] => {
  if (!props.feedingHistory || props.feedingHistory.length === 0) {
    return [new Date().getFullYear()];
  }

  const years = new Set<number>();
  props.feedingHistory.forEach((feeding: Feeding) => {
    const feedingDate = new Date(feeding.date);
    years.add(feedingDate.getFullYear());
  });

  const yearArray = Array.from(years).sort((a: number, b: number) => b - a); // Latest year first
  return yearArray.length > 0 ? yearArray : [new Date().getFullYear()];
});

// Format date for monthly view
const formatDateLong = (dateStr: string): string => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const options: Intl.DateTimeFormatOptions = {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Jakarta'
  };
  return date.toLocaleDateString('id-ID', options);
};

// Get filtered feeding data based on selected month/year
const filteredFeedings = computed((): GroupedFeeding[] => {
  if (!props.feedingHistory || props.feedingHistory.length === 0) {
    return [];
  }

  // Filter feedings for selected month/year and group by date
  const monthlyFeedings = props.feedingHistory.filter((feeding: Feeding) => {
    const feedingDate = new Date(feeding.date);
    return feedingDate.getMonth() === selectedMonth.value && feedingDate.getFullYear() === selectedYear.value;
  });

  const grouped: GroupedFeedings = {};

  monthlyFeedings.forEach((feeding: Feeding) => {
    const date = feeding.date;

    if (!grouped[date]) {
      grouped[date] = {
        date,
        totalQuantity: 0,
        totalQuantityPerLivestock: 0,
        sessions: {},
        livestockCount: feeding.livestock_count || 1
      };
    }

    // Add to total quantity
    const feedQuantity = parseFloat(feeding.quantity.toString());
    const leftoverQuantity = feeding.leftover ? parseFloat((feeding.leftover.leftover_quantity || 0).toString()) : 0;
    const consumedQuantity = feedQuantity - leftoverQuantity;

    grouped[date].totalQuantity += feedQuantity;
    grouped[date].totalQuantityPerLivestock += consumedQuantity / (feeding.livestock_count || 1);

    // Group by session
    if (!grouped[date].sessions[feeding.session]) {
      grouped[date].sessions[feeding.session] = {
        quantity: 0,
        quantityPerLivestock: 0,
        feedings: []
      };
    }
    grouped[date].sessions[feeding.session].quantity += feedQuantity;
    grouped[date].sessions[feeding.session].quantityPerLivestock += consumedQuantity / (feeding.livestock_count || 1);
    grouped[date].sessions[feeding.session].feedings.push(feeding);
  });

  // Convert to array and sort by date (newest first)
  return Object.values(grouped).sort((a: GroupedFeeding, b: GroupedFeeding) => {
    const dateA = a.date ? new Date(a.date).getTime() : 0;
    const dateB = b.date ? new Date(b.date).getTime() : 0;
    return dateB - dateA;
  });
});

const translateSession = (session: string): string => {
  if (!session) return '';

  switch (session.toLowerCase()) {
    case 'morning':
      return 'Pagi';
    case 'afternoon':
      return 'Siang';
    case 'evening':
      return 'Sore';
    case 'night':
      return 'Malam';
    case 'pagi':
      return 'Pagi';
    case 'siang':
      return 'Siang';
    case 'sore':
      return 'Sore';
    case 'malam':
      return 'Malam';
    case 'dawn':
      return 'Subuh';
    case 'dusk':
      return 'Maghrib';
    default:
      return session; // Return original if no translation found
  }
};
</script>