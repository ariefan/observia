<script setup lang="ts">
import { computed } from 'vue';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { X, Share, Calendar } from 'lucide-vue-next';
import LivestockDefault from "@/assets/livestock-default.png";

interface LivestockDetail {
    id: string;
    name: string;
    aifarm_id: string;
    photo: string | null;
    species: string;
    average_litre_per_day?: number;
    total_volume?: number;
    lactation_days?: number;
    current_weight?: number;
    national_rank?: number;
    barn_rank?: number;
    total_national_livestock?: number;
}

interface Props {
    open: boolean;
    livestock: LivestockDetail | null;
    context?: 'milk' | 'weight';
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const closeDialog = () => {
    emit('update:open', false);
};

const getPhotoUrl = (livestock: LivestockDetail) => {
    if (livestock.photo) {
        return `/storage/${livestock.photo}`;
    }
    return LivestockDefault;
};

const formatDate = () => {
    const today = new Date();
    return today.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const shareCard = async () => {
    if (navigator.share && props.livestock) {
        try {
            await navigator.share({
                title: `Produktivitas ${props.livestock.name}`,
                text: `Lihat produktivitas ternak ${props.livestock.name} (${props.livestock.aifarm_id}) di Aifarm`,
                url: window.location.href
            });
        } catch (err) {
            console.log('Error sharing:', err);
        }
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="closeDialog">
        <DialogContent class="p-0 border-0 bg-transparent shadow-none max-w-[580px]">
            <div v-if="livestock" class="relative w-full h-[540px] rounded-[28px] shadow-xl overflow-hidden bg-slate-900/80">
                <!-- Background image -->
                <img
                    :alt="livestock.name"
                    class="absolute inset-0 w-full h-full object-cover"
                    :src="getPhotoUrl(livestock)"
                />

                <!-- Soft vignette + tint -->
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/40"></div>

                <!-- Rounded border -->
                <div class="pointer-events-none absolute inset-0 rounded-[28px] ring-1 ring-black/10"></div>

                <!-- Close button -->
                <button
                    @click="closeDialog"
                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-black/20 hover:bg-black/30 transition backdrop-blur text-white"
                >
                    <X class="w-5 h-5" />
                </button>

                <!-- Top bar content -->
                <div class="absolute top-4 left-4 right-16 flex items-start justify-between">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-600 text-white text-[11px] font-black">A</div>
                        <span class="text-white/90 font-semibold tracking-tight">Aifarm</span>
                    </div>
                    <!-- Title + date -->
                    <div class="text-right">
                        <div class="text-white text-xl font-extrabold drop-shadow-sm">
                            {{ context === 'weight' ? 'Produktivitas Bobot' : 'Produktivitas Susu' }}
                        </div>
                        <div class="text-white/70 text-[12px] -mt-0.5 flex items-center gap-1">
                            <Calendar class="w-3 h-3" />
                            Update {{ formatDate() }}
                        </div>
                    </div>
                </div>

                <!-- Bottom content layer -->
                <div class="absolute inset-x-4 bottom-4">
                    <div class="flex items-end justify-between gap-3">
                        <!-- Left identity block -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h1 class="text-white text-5xl font-semibold leading-none tracking-tight drop-shadow-sm">
                                    {{ livestock.name }}
                                </h1>
                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[12px] font-semibold bg-white/15 text-white/95 ring-1 ring-white/25">
                                    {{ livestock.aifarm_id }}
                                </span>
                            </div>
                            <div class="mt-1 text-white/90 text-sm">{{ livestock.species }}</div>
                        </div>

                        <!-- Share icon -->
                        <button
                            @click="shareCard"
                            class="mb-3 mr-auto ml-4 p-2 rounded-full bg-white/20 hover:bg-white/30 transition ring-1 ring-white/30 backdrop-blur"
                        >
                            <Share class="w-5 h-5 text-white" />
                        </button>

                        <!-- Right stat chips -->
                        <div class="flex gap-3">
                            <!-- National Rank -->
                            <div class="w-[120px] rounded-2xl bg-teal-700/90 backdrop-blur-sm p-4 text-center ring-1 ring-black/10 shadow-md">
                                <div class="text-white text-3xl font-extrabold leading-none">
                                    {{ livestock.national_rank || 'N/A' }}
                                </div>
                                <div class="text-white/80 text-[12px] mt-0.5">
                                    /{{ livestock.total_national_livestock || '10.000' }}
                                </div>
                                <div class="text-white/80 text-[11px] mt-1">Rank Nasional</div>
                            </div>
                            <!-- Performance Metric -->
                            <div class="w-[120px] rounded-2xl bg-teal-600/90 backdrop-blur-sm p-4 text-center ring-1 ring-black/10 shadow-md">
                                <div class="text-white text-3xl font-extrabold leading-none">
                                    {{ livestock.barn_rank || 'N/A' }}
                                </div>
                                <div class="text-white/80 text-[12px] mt-0.5">
                                    <span v-if="context === 'weight'">
                                        {{ livestock.current_weight || '0.0' }} kg
                                    </span>
                                    <span v-else>
                                        {{ livestock.average_litre_per_day || '0.0' }} liter/hari
                                    </span>
                                </div>
                                <div class="text-white/80 text-[11px] mt-1">Rank Kandang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>