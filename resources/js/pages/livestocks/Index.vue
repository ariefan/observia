<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Mars, Venus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { FloatingInput } from '@/components/ui/floating-input';
import { MapInput } from '@/components/ui/map-input';
import { ImageUpload } from '@/components/ui/image-upload';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Example2 from '@/assets/example-2.png';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profil Peternakan',
        href: '/teams',
    },
];

const props = defineProps<{
    name?: string;
}>();

const selectedCoordinates = ref<{ latitude: number; longitude: number } | null>(null);

function handleCoordinateUpdate(coordinates: { latitude: number; longitude: number }) {
    selectedCoordinates.value = coordinates;
    console.log('Coordinates updated:', coordinates);
}

const profileImage = ref(null);

const handlePhotoUpload = ({ blob, url, dimensions }) => {
    console.log('Photo uploaded:', { blob, dimensions });
    // Here you would typically upload the blob to your server
    // For example:
    // const formData = new FormData();
    // formData.append('avatar', blob, 'profile.jpg');
    // fetch('/api/upload-avatar', { method: 'POST', body: formData });
};

const handlePhotoRemove = () => {
    console.log('Photo removed');
    // Handle photo removal, e.g., delete from server
};


const form = useForm({
    name: '',
    email: '',
    location: {
        latitude: 0,
        longitude: 0,
    },
});

function submit() {
}

const show = (id: string) => router.visit(route('livestocks.show', id));

const back = () => window.history.back();

</script>

<template>

    <Head title="Populasi" />

    <AppLayout>
        <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
            <div>
                <h1 class="text-xl font-semibold text-primary">Populasi</h1>
            </div>

            <Card class="border-0 bg-primary">
                <CardContent class="pt-4">
                  <p class="text-white text-sm font-sans mb-2">Populasi ternak jantan dan betina di peternakan Anda:</p>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-white font-sans">
                    <!-- GOAT SECTION -->
                    <div class="rounded-xl flex flex-col">
                        <!-- Header -->
                        <div
                            class="bg-teal-500 dark:bg-teal-200 text-white dark:text-black rounded-t-xl px-4 py-1 flex items-center gap-2">
                            <Mars class="size-4" />
                            <span>Ternak</span>
                        </div>

                        <!-- Big fat number -->
                        <div
                            class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-2 rounded-b-xl text-2xl font-semibold">
                            00 <span class="text-sm font-normal">Ekor</span>
                        </div>
                    </div>

                    <!-- MILK SECTION -->
                    <div class="rounded-xl flex flex-col">
                        <!-- Header -->
                        <div
                            class="bg-cyan-500 dark:bg-cyan-200 text-white dark:text-black rounded-t-xl px-4 py-1 flex items-center gap-2">
                            <Venus class="size-4" />
                            <span>Susu</span>
                        </div>

                        <!-- Big fat number -->
                        <div
                            class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-2 rounded-b-xl text-2xl font-semibold">
                            00 <span class="text-sm font-normal">Liter</span>
                        </div>
                    </div>
                  </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-6 gap-4 text-white font-sans">
              <Card
                @click="show(i)" v-for="i in 10" :key="i"
                class="cursor-pointer border-0 rounded-lg overflow-hidden shadow-md p-0 transition-all duration-200 ease-in-out
                       hover:shadow-2xl hover:scale-[1.03] hover:-translate-y-1 hover:ring-4 hover:ring-primary/30 hover:bg-primary/20"
              >
                <!-- Image section with floating gender icon -->
                <div class="relative">
                  <img
                    :src="Example2"
                    alt="Angel the Goat"
                    class="w-full h-36 object-cover"
                  />

                    <!-- Floating gender icon circle -->
                    <div class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/25 backdrop-blur-md flex items-center justify-center shadow-sm">
                      <span v-if="Math.random() < 0.5" class="text-blue-300"><Mars /></span>
                      <span v-else class="text-pink-300"><Venus /></span>
                    </div>
                    
                    <Badge class="absolute bottom-2 right-2 bg-primary text-white rounded-full">ID-007</Badge>
                </div>

                <!-- Goat info section -->
                <CardContent class="px-4 py-2">
                  <div class="flex items-center justify-between mb-1">
                    <h3 class="text-base font-semibold text-primary">Angel</h3>
                  </div>
                  <p class="text-sm">Etawa Beor</p>
                  <p class="text-sm">2 tahun</p>
                </CardContent>
              </Card>
            </div>
        </div>
    </AppLayout>
</template>
