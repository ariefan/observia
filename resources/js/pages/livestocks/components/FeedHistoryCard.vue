<template>
    <Card class="border border-primary/20 dark:border-primary/80">
      <CardContent class="p-4">
      <h3 class="font-semibold text-lg mb-4">Riwayat Pakan</h3>
      <ul v-if="feedingHistory && feedingHistory.length > 0" class="space-y-2">
        <li v-for="feeding in feedingHistory" :key="`${feeding.id}-${feeding.date}-${feeding.time}`" 
            class="flex justify-between items-start">
          <div>
            <Badge variant="default" class="rounded-full mb-1">{{ feeding.ration?.name || 'Ransum tidak diketahui' }}</Badge>
            <p class="text-sm mt-1">
              {{ feeding.time }} • {{ translateSession(feeding.session) }}
            </p>
            <div v-if="feeding.ration?.rationItems && feeding.ration.rationItems.length > 0" 
                 class="text-xs text-muted-foreground mt-1">
              Komposisi: {{ feeding.ration.rationItems.map(item => `${item.feed} (${item.quantity}kg)`).join(', ') }}
            </div>
            <p v-if="feeding.notes" class="text-xs text-muted-foreground mt-1 italic">
              "{{ feeding.notes }}"
            </p>
          </div>
          <div class="text-right">
            <div class="text-sm mb-1">{{ formatDate(feeding.date) }}</div>
            <Badge :variant="feeding.leftover ? 'destructive' : 'secondary'" class="text-xs">
              {{ feeding.quantity }}kg{{ feeding.leftover ? ` sisa ${feeding.leftover.leftover_quantity}kg` : '' }}
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
</template>
  
<script setup>
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

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
    case 'dawn':
      return 'Subuh';
    case 'dusk':
      return 'Maghrib';
    default:
      return session; // Return original if no translation found
  }
};
</script>
  