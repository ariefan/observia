<template>
    <Card class="border border-primary/20 dark:border-primary/80">
      <CardContent class="p-4">
      <h3 class="font-semibold text-lg mb-4">Riwayat Pakan</h3>
      <ul class="space-y-2">
        <li v-for="item in feed" :key="item.date" class="flex justify-between items-center">
          <div>
            <p class="font-medium">{{ item.name }}</p>
            <p class="text-sm">{{ item.date }}</p>
          </div>
          <div class="flex items-center gap-2">
            <img v-if="item.avatar" :src="item.avatar" class="w-6 h-6 rounded-full" />
            <span class="text-sm">{{ item.qty }}</span>
          </div>
        </li>
      </ul>
    </CardContent>
  </Card>
</template>
  
<script setup>
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
const props = defineProps(['feed'])
</script>
  