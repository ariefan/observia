<script setup lang="ts">
import { computed } from 'vue'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = withDefaults(defineProps<{
  modelValue?: number | null
  max?: number
  class?: HTMLAttributes['class']
  indicatorClass?: string
}>(), {
  modelValue: 0,
  max: 100,
})

const percentage = computed(() => {
  if (props.modelValue == null) return 0
  return Math.min(100, Math.max(0, (props.modelValue / props.max) * 100))
})
</script>

<template>
  <div
    :class="cn('relative h-4 w-full overflow-hidden rounded-full bg-secondary', props.class)"
  >
    <div
      :class="cn('h-full w-full flex-1 bg-primary transition-all', indicatorClass)"
      :style="{ transform: `translateX(-${100 - percentage}%)` }"
    />
  </div>
</template>
