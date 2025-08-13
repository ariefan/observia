<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Megaphone } from 'lucide-vue-next'

defineProps(['type', 'title', 'description']) // type: info/success/warning
</script>

<template>
  <Alert :variant="type" class="mb-2 border-0">
    <Megaphone class="h-4 w-4" />
    <AlertTitle>{{ title }}</AlertTitle>
    <AlertDescription class="text-sm">{{ description }}</AlertDescription>
  </Alert>
</template>
