<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import 'vue-sonner/style.css';

const page = usePage();

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

onMounted(() => {
    if (page.props.flash?.success) {
        toast.success("Berhasil", {
            description: page.props.flash?.success,
        })
    }
    if (page.props.flash?.error) {
        toast("Gagal", {
            description: page.props.flash?.error,
        })
    }
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <div class="h-48"></div>
        <Toaster position="top-right" />
    </AppLayout>
</template>
