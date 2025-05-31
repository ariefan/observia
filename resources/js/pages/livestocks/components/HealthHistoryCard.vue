<template>
    <Card class="border border-primary/20 dark:border-primary/80">
      <CardContent class="p-4">
      <h3 class="font-semibold text-lg mb-4">Riwayat Kesehatan</h3>
      <ul class="space-y-2">
        <li v-for="item in history" :key="item.date" class="flex justify-between items-start">
          <div>
            <Badge v-if="item.status === 'Sehat'" variant="default" class="rounded-full">Sehat</Badge>
            <Badge v-else variant="destructive" class="rounded-full">Sakit</Badge>
            <p class="text-sm mt-1">{{ item.desc }}</p>
          </div>
          <span class="text-sm">{{ item.date }}</span>
        </li>
      </ul>
    </CardContent>
  </Card>
</template>
  
<script setup>
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
const props = defineProps(['history'])
</script>
  