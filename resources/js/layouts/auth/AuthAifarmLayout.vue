<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import Autoplay from 'embla-carousel-autoplay';
import { Carousel, type CarouselApi, CarouselContent, CarouselItem } from '@/components/ui/carousel';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { watchOnce } from '@vueuse/core';
import { ref, computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { Sun, Moon } from 'lucide-vue-next';
import Landing1 from '@/assets/landing-1.png';
import Landing2 from '@/assets/landing-2.png';
import Landing3 from '@/assets/landing-3.png';

defineProps<{
    title?: string;
    description?: string;
}>();

const emblaMainApi = ref<CarouselApi>();
const emblaThumbnailApi = ref<CarouselApi>();
const selectedIndex = ref(0);

function onSelect() {
    if (!emblaMainApi.value || !emblaThumbnailApi.value)
        return;
    selectedIndex.value = emblaMainApi.value.selectedScrollSnap();
    emblaThumbnailApi.value.scrollTo(emblaMainApi.value.selectedScrollSnap());
}

function onThumbClick(index: number) {
    if (!emblaMainApi.value || !emblaThumbnailApi.value)
        return;
    emblaMainApi.value.scrollTo(index);
}

watchOnce(emblaMainApi, (emblaMainApi) => {
    if (!emblaMainApi)
        return;

    onSelect();
    emblaMainApi.on('select', onSelect);
    emblaMainApi.on('reInit', onSelect);
});

const plugin = Autoplay({
    delay: 3000,
    stopOnMouseEnter: true,
    stopOnInteraction: false,
});

// Theme toggle functionality
type Appearance = 'light' | 'dark';
const { appearance, updateAppearance } = useAppearance();

function toggleAppearanceMode() {
    const nextMode: Appearance = appearance.value === 'light' ? 'dark' : 'light';
    updateAppearance(nextMode);
}

const getThemeIcon = computed(() => {
    return appearance.value === 'light' ? Sun : Moon;
});

const getThemeLabel = computed(() => {
    return appearance.value === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
});
</script>

<template>
    <div class="h-dvh p-8 bg-white dark:bg-slate-900">
        <div
            class="relative grid h-full flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0 lg:border-2 border-primary-500 rounded-xl">
            <div class="relative hidden flex-col p-10 dark:text-white lg:flex">
                <div class="w-full sm:w-auto">
                    <Carousel class="relative w-full max-w-lg mx-auto" :plugins="[plugin]" @mouseenter="plugin.stop"
                        @mouseleave="[plugin.reset(), plugin.play(), console.log('Running')];"
                        @init-api="(val) => emblaMainApi = val">
                        <CarouselContent>
                            <CarouselItem>
                                <img :src="Landing1" alt="Landing 1" class="">
                                <div class="text-center text-xs mt-2">
                                    <h3 class="text-lg font-semibold">Selamat datang</h3>
                                    <p class="my-2">Aifarm - Kunci keberhasilan untuk peternakan anda</p>
                                </div>
                            </CarouselItem>
                            <CarouselItem>
                                <img :src="Landing2" alt="Landing 2" class="">
                                <div class="text-center text-xs mt-2">
                                    <h3 class="text-lg font-semibold">Optimalkan hasil peternakan</h3>
                                    <p class="my-2">Aifarm - Solusi cerdas untuk manajemen peternakan kambing, produksi
                                        susu, dan
                                        kembangbiak peternakan anda</p>
                                </div>
                            </CarouselItem>
                            <CarouselItem>
                                <img :src="Landing3" alt="Landing 3" class="">
                                <div class="text-center text-xs mt-2">
                                    <h3 class="text-lg font-semibold">Capai keberhasilan bisnis peternakan</h3>
                                    <p class="my-2">Aifarm - Memberikan kemudahan dalam analisa bisnis peternakanmu
                                        untuk
                                        meningkatkan produktifitas dan efektifitas</p>
                                </div>
                            </CarouselItem>
                        </CarouselContent>
                    </Carousel>

                    <Carousel class="relative w-full max-w-xs mx-auto" @init-api="(val) => emblaThumbnailApi = val">
                        <CarouselContent class="flex gap-1 ml-0 justify-center">
                            <div v-for="(_, index) in 3" :key="index" class="p-1"
                                :class="index === selectedIndex ? '' : 'opacity-50'">
                                <Button @click="onThumbClick(index)"
                                    :variant="index === selectedIndex ? 'default' : 'outline'"
                                    class="size-2.5 p-0 border border-primary rounded-full"></Button>
                            </div>
                        </CarouselContent>
                    </Carousel>
                </div>
            </div>
            <div class="lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <div class="flex justify-between">
                        <AppLogo class="mr-2 size-8 fill-current dark:text-white" />
                        <div class="flex items-center gap-2">
                            <TooltipProvider :delay-duration="0">
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button @click="toggleAppearanceMode" variant="outline" size="icon">
                                            <span class="sr-only">{{ getThemeLabel }}</span>
                                            <component :is="getThemeIcon" class="size-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{{ getThemeLabel }}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                            <Button variant="outline" size="icon" class="text-xs">ID</Button>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2 text-center">
                        <h1 class="text-2xl font-semibold tracking-tight" v-if="title">{{ title }}</h1>
                        <p class="text-sm text-muted-foreground" v-if="description">{{ description }}</p>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
