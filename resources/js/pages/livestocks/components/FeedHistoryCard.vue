<template>
    <Card class="border border-primary/20 dark:border-primary/80">
      <CardContent class="p-4">
      <h3 class="font-semibold text-lg mb-4">Riwayat Pakan</h3>
      <div v-if="feedingHistory && feedingHistory.length > 0" class="space-y-3">
        <div v-for="feeding in feedingHistory" :key="`${feeding.id}-${feeding.date}-${feeding.time}`" 
             class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800/50">
          <div class="flex justify-between items-start mb-2">
            <div class="flex-1">
              <p class="font-medium text-sm">{{ feeding.ration?.name || 'Ransum tidak diketahui' }}</p>
              <p class="text-xs text-muted-foreground">
                {{ formatDate(feeding.date) }} • {{ feeding.time }} • {{ feeding.session }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium">{{ feeding.quantity }} kg</p>
              <p v-if="feeding.leftover" class="text-xs text-orange-600 dark:text-orange-400">
                Sisa: {{ feeding.leftover.quantity }} kg
              </p>
            </div>
          </div>
          
          <div v-if="feeding.ration?.rationItems && feeding.ration.rationItems.length > 0" 
               class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs text-muted-foreground mb-1">Komposisi:</p>
            <div class="flex flex-wrap gap-1">
              <span v-for="item in feeding.ration.rationItems" 
                    :key="item.id" 
                    class="inline-block px-2 py-0.5 bg-primary/10 text-primary rounded-full text-xs">
                {{ item.feed }} ({{ item.quantity }} kg)
              </span>
            </div>
          </div>
          
          <div v-if="feeding.user" class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="w-5 h-5 bg-primary rounded-full flex items-center justify-center text-xs text-white">
              {{ feeding.user.name?.charAt(0) || 'U' }}
            </div>
            <span class="text-xs text-muted-foreground">Oleh {{ feeding.user.name }}</span>
          </div>
          
          <p v-if="feeding.notes" class="text-xs text-muted-foreground mt-2 italic">
            "{{ feeding.notes }}"
          </p>
        </div>
      </div>
      
      <div v-else-if="feed && feed.length > 0" class="space-y-2">
        <div v-for="item in feed" :key="item.date" class="flex justify-between items-center">
          <div>
            <p class="font-medium">{{ item.name }}</p>
            <p class="text-sm text-muted-foreground">{{ item.date }}</p>
          </div>
          <div class="flex items-center gap-2">
            <img v-if="item.avatar" :src="item.avatar" class="w-6 h-6 rounded-full" />
            <span class="text-sm">{{ item.qty }}</span>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-8 text-muted-foreground">
        <p class="text-sm">Belum ada riwayat pakan</p>
        <p class="text-xs">Data pemberian pakan akan muncul di sini</p>
      </div>
    </CardContent>
  </Card>
</template>
  
<script setup>
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";

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
</script>
  