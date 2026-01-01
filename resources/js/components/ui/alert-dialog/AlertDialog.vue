<script setup lang="ts">
import { AlertDialogRoot } from 'reka-ui'

interface Props {
  open?: boolean
  defaultOpen?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  open: undefined,
  defaultOpen: false,
})

const emits = defineEmits<{
  'update:open': [value: boolean]
}>()
</script>

<template>
  <AlertDialogRoot
    :open="props.open"
    :default-open="props.defaultOpen"
    @update:open="emits('update:open', $event)"
  >
    <slot />
  </AlertDialogRoot>
</template>
