<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Calendar, Share2, Download } from 'lucide-vue-next';
import { ref } from 'vue';
import LivestockDefault from "@/assets/livestock-default.png";
import AifarmLogo from "@/assets/logo.png";
import html2canvas from 'html2canvas';

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
    farm?: {
        name: string;
        image?: string;
    };
}

interface Props {
    livestock: LivestockDetail;
    context?: 'milk' | 'weight';
}

const props = defineProps<Props>();

const cardRef = ref<HTMLElement | null>(null);
const isDownloading = ref(false);

const getPhotoUrl = (livestock: LivestockDetail) => {
    if (livestock.photo) {
        let photoUrl;
        // Handle different photo path formats
        if (livestock.photo.startsWith('http')) {
            // Already a full URL
            photoUrl = livestock.photo;
        } else if (livestock.photo.startsWith('/storage/')) {
            // Already has /storage/ prefix
            photoUrl = livestock.photo;
        } else if (livestock.photo.startsWith('storage/')) {
            // Has storage/ prefix without leading slash
            photoUrl = `/${livestock.photo}`;
        } else {
            // Assume it's just the filename, add /storage/
            photoUrl = `/storage/${livestock.photo}`;
        }
        return photoUrl;
    }
    return LivestockDefault;
};

const getFarmImageUrl = (image?: string | null) => {
    if (!image) {
        return null;
    }
    if (image.startsWith('http')) {
        return image;
    }
    if (image.startsWith('/storage/')) {
        return image;
    }
    if (image.startsWith('storage/')) {
        return `/${image}`;
    }
    return `/storage/${image}`;
};

const formatDate = () => {
    const today = new Date();
    const day = today.getDate().toString().padStart(2, '0');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const month = months[today.getMonth()];
    const year = today.getFullYear();
    const hours = today.getHours().toString().padStart(2, '0');
    const minutes = today.getMinutes().toString().padStart(2, '0');
    const seconds = today.getSeconds().toString().padStart(2, '0');
    return `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
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

const downloadCard = async () => {
    if (!cardRef.value || !props.livestock) return;

    try {
        isDownloading.value = true;

        // Hide buttons during capture
        const buttons = cardRef.value.querySelectorAll('.download-exclude');
        buttons.forEach((btn) => {
            (btn as HTMLElement).style.display = 'none';
        });

        // Capture the card
        const canvas = await html2canvas(cardRef.value, {
            backgroundColor: null,
            scale: 2, // Higher quality
            useCORS: true,
            logging: false,
        });

        // Show buttons again
        buttons.forEach((btn) => {
            (btn as HTMLElement).style.display = '';
        });

        // Convert to blob and download
        canvas.toBlob((blob) => {
            if (blob) {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `${props.livestock?.name}-${props.livestock?.aifarm_id}-${formatDate()}.png`;
                link.click();
                URL.revokeObjectURL(url);
            }
            isDownloading.value = false;
        }, 'image/png');

    } catch (err) {
        console.error('Error downloading card:', err);
        isDownloading.value = false;
    }
};
</script>

<template>

    <Head>
        <title>{{ livestock.name }} - {{ livestock.aifarm_id }}</title>
        <meta name="description" :content="`Produktivitas ternak ${livestock.name} (${livestock.aifarm_id}) di Aifarm`">
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex flex-col items-center justify-center p-4 gap-4">
        <div v-if="livestock" ref="cardRef"
            class="relative w-full max-w-[580px] h-[540px] rounded-[28px] shadow-xl overflow-hidden bg-slate-900/80">
            <!-- Background image -->
            <img :alt="livestock.name" class="absolute inset-0 w-full h-full object-cover" :src="getPhotoUrl(livestock)"
                @error="$event.target.src = LivestockDefault" />

            <!-- Soft vignette + tint -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/40"></div>

            <!-- Black gradient overlays -->
            <div class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-b from-black/30 to-transparent pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div>

            <!-- Rounded border -->
            <div class="pointer-events-none absolute inset-0 rounded-[28px] ring-1 ring-black/10"></div>

            <!-- Top bar content -->
            <div class="absolute top-4 left-4 right-4 flex items-start justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <img :src="AifarmLogo" alt="Aifarm" class="h-16 w-auto" />
                </div>
                <!-- Farm logo and name -->
                <div class="text-right flex flex-col items-end gap-1">
                    <img v-if="getFarmImageUrl(livestock.farm?.image)"
                        :src="getFarmImageUrl(livestock.farm?.image)"
                        :alt="livestock.farm?.name || 'Farm'"
                        class="h-12 w-auto"
                        @error="($event.target as HTMLImageElement).style.display='none'" />
                    <div v-if="livestock.farm?.name" class="text-white text-sm font-semibold drop-shadow-sm">
                        {{ livestock.farm.name }}
                    </div>
                </div>
            </div>

            <!-- Bottom content layer -->
            <div class="absolute inset-x-4 bottom-4">
                <div class="flex items-end justify-between gap-3">
                    <!-- Left identity block -->
                    <div class="min-w-0">
                        <h1 class="text-white text-5xl font-semibold leading-none tracking-tight drop-shadow-sm">
                            {{ livestock.name }}
                        </h1>
                        <div class="flex items-center gap-2 my-2">
                            <span
                                class="shrink-0 px-2 py-0.5 rounded-full text-[12px] font-semibold bg-teal-500/40 text-white/95 ring-1 ring-teal-400/30 backdrop-blur">
                                {{ livestock.aifarm_id }}
                            </span>
                        </div>
                        <div class="text-white/90 text-sm">{{ livestock.species }}</div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mb-3 mr-auto ml-4 flex gap-2">
                        <!-- Share button -->
                        <button @click="shareCard"
                            class="download-exclude p-2 rounded-full bg-white/20 hover:bg-white/30 transition ring-1 ring-white/30 backdrop-blur">
                            <Share2 class="w-5 h-5 text-white" />
                        </button>

                        <!-- Download button -->
                        <button @click="downloadCard" :disabled="isDownloading"
                            class="download-exclude p-2 rounded-full bg-white/20 hover:bg-white/30 transition ring-1 ring-white/30 backdrop-blur disabled:opacity-50 disabled:cursor-not-allowed">
                            <Download class="w-5 h-5 text-white" />
                        </button>
                    </div>

                    <!-- Right stat chips and title -->
                    <div class="flex flex-col items-end gap-3">
                        <!-- Stat chips -->
                        <div class="flex gap-3">
                            <!-- National Rank -->
                            <div
                                class="hidden w-[120px] rounded-2xl bg-teal-700/90 backdrop-blur-sm p-4 text-center ring-1 ring-black/10 shadow-md">
                                <div class="text-white text-3xl font-extrabold leading-none">
                                    {{ livestock.national_rank || 'N/A' }}
                                </div>
                                <div class="text-white/80 text-[12px] mt-0.5">
                                    /{{ livestock.total_national_livestock || '10.000' }}
                                </div>
                                <div class="text-white/80 text-[11px] mt-1">Rank Nasional</div>
                            </div>
                            <!-- Performance Metric -->
                            <div
                                class="w-[120px] rounded-2xl bg-teal-600/90 backdrop-blur-sm p-4 text-center ring-1 ring-black/10 shadow-md">
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

                        <!-- Title + date -->
                        <div class="text-right">
                            <div class="text-white text-xl font-extrabold drop-shadow-sm">
                                {{ context === 'weight' ? 'Produktivitas Bobot' : 'Produktivitas Susu' }}
                            </div>
                            <div class="text-white/70 text-[12px] -mt-0.5">
                                Update {{ formatDate() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lihat Detail Button -->
        <a v-if="livestock"
            :href="`/livestock/${livestock.id}`"
            class="download-exclude px-6 py-3 bg-white hover:bg-gray-50 text-gray-900 font-semibold rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Lihat Detail
        </a>
    </div>
</template>
