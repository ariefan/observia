<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LivestockCard from '@/components/LivestockCard.vue';
import type { LivestockDetail } from '@/types';

interface Props {
    livestock: LivestockDetail;
    context?: 'milk' | 'weight';
}

defineProps<Props>();
</script>

<template>
    <Head>
        <title>{{ livestock.name }} - {{ livestock.aifarm_id }}</title>
        <meta name="description" :content="`Produktivitas ternak ${livestock.name} (${livestock.aifarm_id}) di Aifarm`">
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex flex-col items-center justify-center p-4 gap-4">
        <div class="w-full max-w-[580px]">
            <LivestockCard v-if="livestock" :livestock="livestock" :context="context" />
        </div>

        <!-- Lihat Detail Button -->
        <a v-if="livestock" :href="`/livestock/${livestock.id}`"
            class="download-exclude px-6 py-3 bg-white hover:bg-gray-50 text-gray-900 font-semibold rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Lihat Detail
        </a>
    </div>
</template>