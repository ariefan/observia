<template>
  <div v-if="error" class="mt-1">
    <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  error?: string | string[];
}

defineProps<Props>();
</script>