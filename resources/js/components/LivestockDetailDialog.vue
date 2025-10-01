<script setup lang="ts">
import { Dialog, DialogContent } from '@/components/ui/dialog';
import LivestockCard from '@/components/LivestockCard.vue';
import type { LivestockDetail } from '@/types';

interface Props {
    open: boolean;
    livestock: LivestockDetail | null;
    context?: 'milk' | 'weight';
}

defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const closeDialog = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="closeDialog">
        <DialogContent class="p-0 border-0 bg-transparent shadow-none max-w-[580px]">
            <LivestockCard v-if="livestock" :livestock="livestock" :context="context" :show-close-button="true" @close="closeDialog" />
        </DialogContent>
    </Dialog>
</template>