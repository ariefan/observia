<script setup lang="ts">
import { ref } from 'vue';
import { X, Share2, Download } from 'lucide-vue-next';
import LivestockDefault from "@/assets/livestock-default.png";
import AifarmLogo from "@/assets/logo.png";
import html2canvas from 'html2canvas';
import type { LivestockDetail } from '@/types';

interface Props {
    livestock: LivestockDetail;
    context?: 'milk' | 'weight';
    showCloseButton?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    context: 'milk',
    showCloseButton: false,
});

const emit = defineEmits<{ (e: 'close'): void; }>();

const getPhotoUrl = (livestock: LivestockDetail) => {
    if (livestock.photo) {
        let photoUrl;
        if (livestock.photo.startsWith('http')) {
            photoUrl = livestock.photo;
        } else if (livestock.photo.startsWith('/storage/')) {
            photoUrl = livestock.photo;
        } else if (livestock.photo.startsWith('storage/')) {
            photoUrl = `/${livestock.photo}`;
        } else {
            photoUrl = `/storage/${livestock.photo}`;
        }
        return photoUrl;
    }
    return LivestockDefault;
};

const getFarmImageUrl = (image?: string | null) => {
    if (!image) return null;
    if (image.startsWith('http')) return image;
    if (image.startsWith('/storage/')) return image;
    if (image.startsWith('storage/')) return `/${image}`;
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

const cardRef = ref<HTMLElement | null>(null);
const isDownloading = ref(false);

const shareCard = async () => {
    if (navigator.share && props.livestock) {
        try {
            const shareUrl = `${window.location.origin}/livestock/${props.livestock.id}?context=${props.context}`;
            await navigator.share({
                title: `Produktivitas ${props.livestock.name}`,
                text: `Lihat produktivitas ternak ${props.livestock.name} (${props.livestock.aifarm_id}) di Aifarm`,
                url: shareUrl
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
        const buttons = cardRef.value.querySelectorAll('.download-exclude');
        buttons.forEach(btn => { (btn as HTMLElement).style.display = 'none'; });

        const canvas = await html2canvas(cardRef.value, {
            backgroundColor: null,
            scale: 2,
            useCORS: true,
            logging: false,
        });

        buttons.forEach(btn => { (btn as HTMLElement).style.display = ''; });

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
    <div ref="cardRef" class="relative w-full h-[540px] rounded-[28px] shadow-xl overflow-hidden bg-slate-900/80">
        <img :alt="livestock.name" class="absolute inset-0 w-full h-full object-cover" :src="getPhotoUrl(livestock)" @error="$event.target.src = LivestockDefault" />
        <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/40"></div>
        <div class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-b from-white/10 to-transparent pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white/10 to-transparent pointer-events-none"></div>
        <div class="pointer-events-none absolute inset-0 rounded-[28px] ring-1 ring-black/10"></div>

        <button v-if="showCloseButton" @click="emit('close')" class="download-exclude absolute top-4 right-4 z-10 p-2 rounded-full bg-black/20 hover:bg-black/30 transition backdrop-blur text-white">
            <X class="w-5 h-5" />
        </button>

        <div class="absolute top-4 left-4 right-16 flex items-start justify-between">
            <div class="flex items-center">
                <img :src="AifarmLogo" alt="Aifarm" class="h-16 w-auto" />
            </div>
            <div class="text-right flex flex-col items-end gap-1">
                <img v-if="getFarmImageUrl(livestock.farm?.image)" :src="getFarmImageUrl(livestock.farm?.image)" :alt="livestock.farm?.name || 'Farm'" class="h-12 w-auto" @error="($event.target as HTMLImageElement).style.display = 'none'" />
                <div v-if="livestock.farm?.name" class="text-white text-sm font-semibold drop-shadow-sm">
                    {{ livestock.farm.name }}
                </div>
            </div>
        </div>

        <div class="absolute inset-x-4 bottom-4">
            <div class="flex items-end justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-white text-5xl font-semibold leading-none tracking-tight drop-shadow-sm">{{ livestock.name }}</h1>
                    <div class="flex items-center gap-2 my-2">
                        <span class="shrink-0 px-2 py-0.5 rounded-full text-[12px] font-semibold bg-teal-500/40 text-white/95 ring-1 ring-teal-400/30 backdrop-blur">
                            {{ livestock.aifarm_id }}
                        </span>
                    </div>
                    <div class="text-white/90 text-sm">{{ livestock.species }}</div>
                </div>

                <div class="mb-3 mr-auto ml-4 flex gap-2">
                    <button @click="shareCard" class="download-exclude p-2 rounded-full bg-white/20 hover:bg-white/30 transition ring-1 ring-white/30 backdrop-blur">
                        <Share2 class="w-5 h-5 text-white" />
                    </button>
                    <button @click="downloadCard" :disabled="isDownloading" class="download-exclude p-2 rounded-full bg-white/20 hover:bg-white/30 transition ring-1 ring-white/30 backdrop-blur disabled:opacity-50 disabled:cursor-not-allowed">
                        <Download class="w-5 h-5 text-white" />
                    </button>
                </div>

                <div class="flex flex-col items-end gap-3">
                    <div class="flex gap-3">
                        <div class="hidden w-[120px] rounded-2xl bg-teal-700/90 backdrop-blur-sm p-4 text-center ring-1 ring-black/10 shadow-md">
                            <div class="text-white text-3xl font-extrabold leading-none">{{ livestock.national_rank || 'N/A' }}</div>
                            <div class="text-white/80 text-[12px] mt-0.5">/{{ livestock.total_national_livestock || '10.000' }}</div>
                            <div class="text-white/80 text-[11px] mt-1">Rank Nasional</div>
                        </div>
                        <div class="w-[120px] rounded-2xl bg-teal-600/90 backdrop-blur-sm p-4 text-center ring-1 ring-black/10 shadow-md">
                            <div class="text-white text-3xl font-extrabold leading-none">{{ livestock.barn_rank || 'N/A' }}</div>
                            <div class="text-white/80 text-[12px] mt-0.5">
                                <span v-if="context === 'weight'">{{ livestock.current_weight || '0.0' }} kg</span>
                                <span v-else>{{ livestock.average_litre_per_day || '0.0' }} liter/hari</span>
                            </div>
                            <div class="text-white/80 text-[11px] mt-1">Rank Kandang</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-white text-xl font-extrabold drop-shadow-sm">{{ context === 'weight' ? 'Produktivitas Bobot' : 'Produktivitas Susu' }}</div>
                        <div class="text-white/70 text-[12px] -mt-0.5">Update {{ formatDate() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
