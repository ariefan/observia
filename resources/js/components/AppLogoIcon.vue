<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import LogoIcon from '@/assets/logo-icon.png';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img :src="LogoIcon" alt="Logo Icon">
</template>
