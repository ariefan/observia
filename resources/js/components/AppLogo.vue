<script setup lang="ts">
import Logo from '@/assets/logo.png';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <!-- <div class="flex aspect-square size-8 items-center justify-center rounded-md text-sidebar-primary-foreground"
        v-if="useSidebar().state.value == 'collapsed'">
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div> -->
    <div class="ml-1 grid flex-1 text-left text-sm">
        <img :src="Logo" alt="Logo">
    </div>
</template>
