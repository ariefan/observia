<script setup lang="ts">
import { AlertDialogTrigger } from 'reka-ui'

interface Props {
  asChild?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  asChild: false,
})
</script>

<template>
  <AlertDialogTrigger :as-child="props.asChild">
    <slot />
  </AlertDialogTrigger>
</template>
