<script lang="ts" setup>
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <li :class="cn('inline-flex items-center gap-1.5 text-lg', props.class)">
        <slot />
    </li>
</template>
