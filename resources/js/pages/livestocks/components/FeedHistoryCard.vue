<template>
  <Card class="border border-primary/20 dark:border-primary/80">
    <CardContent class="p-4">
      <h3 class="font-semibold text-lg mb-4">Riwayat Pakan</h3>
      <ul v-if="groupedFeedings && Object.keys(groupedFeedings).length > 0" class="space-y-3">
        <li v-for="(dateGroup, date) in groupedFeedings" :key="date"
          class="border-b pb-3 last:border-b-0 cursor-pointer hover:bg-muted/50 rounded-lg p-2 -m-2"
          @click="openDetailDialog(date, dateGroup)">
          <div class="flex justify-between items-start mb-2">
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
              <div class="text-sm font-medium">{{ formatDate(date) }}</div>
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
  </Card>

  <!-- Detail Dialog -->
  <Dialog v-model:open="showDialog">
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>Detail Pakan - {{ formatDate(selectedDate) }}</DialogTitle>
        <DialogDescription>
          Rincian pemberian pakan pada tanggal {{ formatDate(selectedDate) }}
        </DialogDescription>
      </DialogHeader>

      <div v-if="selectedDateData" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <h4 class="font-semibold mb-2">Total Pakan</h4>
            <p class="text-2xl font-bold text-primary">{{ selectedDateData.totalQuantity }} kg</p>
          </div>
          <div>
            <h4 class="font-semibold mb-2">Jumlah Sesi</h4>
            <p class="text-2xl font-bold text-primary">{{ Object.keys(selectedDateData.sessions).length }}</p>
          </div>
        </div>

        <div>
          <h4 class="font-semibold mb-3">Rincian Per Sesi</h4>
          <div class="space-y-3">
            <div v-for="session in ['morning', 'afternoon', 'evening', 'night']" :key="session"
              class="border rounded-lg p-3"
              :class="selectedDateData.sessions[session] ? '' : 'bg-muted/30'">
              <div class="flex justify-between items-center mb-2">
                <Badge :variant="selectedDateData.sessions[session] ? 'default' : 'secondary'">
                  {{ translateSession(session) }}
                </Badge>
                <span class="font-semibold">
                  {{ selectedDateData.sessions[session]?.quantity || 0 }} kg
                </span>
              </div>
              
              <!-- Show feeding data if exists -->
              <div v-if="selectedDateData.sessions[session]" class="space-y-2">
                <div v-for="feeding in selectedDateData.sessions[session].feedings" :key="feeding.id"
                  class="text-sm border-l-2 border-primary/30 pl-3 bg-background/50 rounded-r p-2">
                  <div class="flex justify-between items-start mb-1">
                    <div>
                      <p class="font-medium text-primary">{{ feeding.ration?.name || 'Ransum tidak diketahui' }}</p>
                      <p class="text-muted-foreground text-xs">{{ feeding.time || 'Waktu tidak tercatat' }}</p>
                    </div>
                    <Badge variant="outline" class="text-xs">
                      {{ feeding.quantity }} kg
                    </Badge>
                  </div>
                  
                  <!-- Show ration composition -->
                  <div v-if="feeding.ration?.rationItems && feeding.ration.rationItems.length > 0"
                    class="text-xs text-muted-foreground mt-2 bg-muted/50 rounded p-2">
                    <p class="font-medium mb-1">Komposisi Ransum:</p>
                    <div class="grid grid-cols-1 gap-1">
                      <div v-for="item in feeding.ration.rationItems" :key="item.id"
                        class="flex justify-between items-center">
                        <span>{{ item.feed }}</span>
                        <span class="font-medium">{{ item.quantity }} kg</span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Show notes if exists -->
                  <p v-if="feeding.notes" class="text-xs italic mt-2 text-muted-foreground bg-muted/30 rounded p-2">
                    "{{ feeding.notes }}"
                  </p>
                </div>
              </div>
              
              <!-- Show no data message -->
              <div v-else class="text-center py-4">
                <p class="text-sm text-muted-foreground italic">Tidak ada pemberian pakan</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="selectedDateData.rations.length > 0">
          <h4 class="font-semibold mb-3">Ringkasan Ransum</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div v-for="ration in selectedDateData.rations" :key="ration.name"
              class="flex justify-between items-center p-2 bg-muted rounded">
              <span class="text-sm font-medium">{{ ration.name }}</span>
              <span class="text-sm font-semibold">{{ ration.totalQuantity }} kg</span>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button @click="showDialog = false" variant="outline">Tutup</Button>
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
const showDialog = ref(false);
const selectedDate = ref('');
const selectedDateData = ref(null);

const openDetailDialog = (date, dateData) => {
  selectedDate.value = date;
  selectedDateData.value = dateData;
  showDialog.value = true;
};

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