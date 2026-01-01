<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { AlertDialogTitle } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <AlertDialogTitle
    :class="cn('text-lg font-semibold dark:text-slate-100', props.class)"
  >
    <slot />
  </AlertDialogTitle>
</template>
