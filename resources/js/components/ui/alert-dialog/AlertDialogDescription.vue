<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { AlertDialogDescription } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <AlertDialogDescription :class="cn('text-sm text-slate-500 dark:text-slate-400', props.class)">
    <slot />
  </AlertDialogDescription>
</template>
