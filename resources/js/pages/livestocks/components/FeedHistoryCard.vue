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
                  ({{ dateGroup.sessions[session].quantity }}kg)
                </span>
              </Badge>
            </div>
            <div class="text-right">
              <div class="text-sm font-semibold">{{ formatDate(date) }}</div>
              <div class="text-sm font-semibold text-muted-foreground">
                <Badge class="text-xs">
                  {{ dateGroup.totalQuantity }} kg
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
              Komposisi: {{feeding.ration.rationItems.map(item => `${item.feed} (${item.quantity}kg)`).join(', ')}}
            </div>
            <p v-if="feeding.notes" class="text-xs text-muted-foreground mt-1 italic">
              "{{ feeding.notes }}"
            </p>
          </div>
          <div class="text-right">
            <div class="text-sm mb-1">{{ formatDate(feeding.date) }}</div>
            <Badge :variant="feeding.leftover ? 'secondary' : 'secondary'" class="text-xs">
              {{ feeding.quantity }}kg
            </Badge>
          </div>
        </li>
      </ul>

      <ul v-else-if="feed && feed.length > 0" class="space-y-2">
        <li v-for="item in feed" :key="item.date" class="flex justify-between items-start">
          <div>
            <Badge variant="default" class="rounded-full mb-1">{{ item.name }}</Badge>
            <p class="text-sm mt-1">{{ item.qty }}</p>
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
      <Button @click="openLast30DaysDialog" variant="ghost"
        class="w-full justify-center rounded-none text-primary hover:bg-primary/10 py-3" style="box-shadow: none;">
        <span class="font-semibold underline">Lihat 30 hari terakhir</span>
      </Button>
    </CardFooter>
  </Card>

  <!-- Last 30 Days Feed Dialog -->
  <Dialog v-model:open="showLast30DaysDialog">
    <DialogContent class="max-w-xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Riwayat Pakan - 30 Hari Terakhir</DialogTitle>
        <DialogDescription>
          Seluruh pemberian pakan dalam 30 hari terakhir
        </DialogDescription>
      </DialogHeader>

      <div v-if="last30DaysFeedings && last30DaysFeedings.length > 0" class="space-y-4">
        <div v-for="dayData in last30DaysFeedings" :key="dayData.date" class="last:border-b-0">
          <div class="flex justify-between items-center">
            <p class="font-semibold text-sm">{{ formatDateLong(dayData.date) }}</p>
            <Badge class="text-xs">
              {{ dayData.totalQuantity.toFixed(1) }} kg
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
                  <span class="font-semibold">{{ feeding.quantity }} kg</span>
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
        <p class="text-sm">Belum ada riwayat pakan dalam 30 hari terakhir</p>
      </div>

      <DialogFooter>
        <Button @click="showLast30DaysDialog = false" variant="secondary">Tutup</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";

const props = defineProps({
  feed: {
    type: Array,
    default: () => []
  },
  feedingHistory: {
    type: Array,
    default: () => []
  }
});

// Group feeding history by date
const groupedFeedings = computed(() => {
  if (!props.feedingHistory || props.feedingHistory.length === 0) {
    return {};
  }

  const grouped = {};

  props.feedingHistory.forEach(feeding => {
    const date = feeding.date;

    if (!grouped[date]) {
      grouped[date] = {
        totalQuantity: 0,
        sessions: {},
        rations: {}
      };
    }

    // Add to total quantity
    grouped[date].totalQuantity += parseFloat(feeding.quantity);

    // Group by session
    if (!grouped[date].sessions[feeding.session]) {
      grouped[date].sessions[feeding.session] = {
        quantity: 0,
        feedings: []
      };
    }
    grouped[date].sessions[feeding.session].quantity += parseFloat(feeding.quantity);
    grouped[date].sessions[feeding.session].feedings.push(feeding);

    // Group by ration
    const rationName = feeding.ration?.name || 'Ransum tidak diketahui';
    if (!grouped[date].rations[rationName]) {
      grouped[date].rations[rationName] = {
        name: rationName,
        totalQuantity: 0
      };
    }
    grouped[date].rations[rationName].totalQuantity += parseFloat(feeding.quantity);
  });

  // Convert rations object to array for easier templating
  Object.keys(grouped).forEach(date => {
    grouped[date].rations = Object.values(grouped[date].rations);
  });

  return grouped;
});

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

// Dialog state
const showLast30DaysDialog = ref(false);

const openLast30DaysDialog = () => {
  showLast30DaysDialog.value = true;
};

// Format date for monthly view
const formatDateLong = (dateStr) => {
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

// Get last 30 days feeding data
const last30DaysFeedings = computed(() => {
  if (!props.feedingHistory || props.feedingHistory.length === 0) {
    return [];
  }

  const now = new Date();
  const thirtyDaysAgo = new Date(now.getTime() - (30 * 24 * 60 * 60 * 1000));

  // Filter feedings for last 30 days and group by date
  const last30DaysFeedings = props.feedingHistory.filter(feeding => {
    const feedingDate = new Date(feeding.date);
    return feedingDate >= thirtyDaysAgo && feedingDate <= now;
  });

  const grouped = {};

  last30DaysFeedings.forEach(feeding => {
    const date = feeding.date;

    if (!grouped[date]) {
      grouped[date] = {
        date,
        totalQuantity: 0,
        sessions: {}
      };
    }

    // Add to total quantity
    grouped[date].totalQuantity += parseFloat(feeding.quantity);

    // Group by session
    if (!grouped[date].sessions[feeding.session]) {
      grouped[date].sessions[feeding.session] = {
        quantity: 0,
        feedings: []
      };
    }
    grouped[date].sessions[feeding.session].quantity += parseFloat(feeding.quantity);
    grouped[date].sessions[feeding.session].feedings.push(feeding);
  });

  // Convert to array and sort by date (newest first)
  return Object.values(grouped).sort((a, b) => new Date(b.date) - new Date(a.date));
});

const translateSession = (session) => {
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