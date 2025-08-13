<script setup lang="ts">
import LogoIcon from '@/assets/logo-icon.png';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: string;
}

defineProps<Props>();
</script>

<template>
    <img :src="LogoIcon" alt="Logo Icon">
</template>
