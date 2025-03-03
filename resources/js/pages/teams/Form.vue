<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profil Peternakan',
        href: '/teams',
    },
];

defineProps<{
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
</script>

<template>

    <Head title="Peternakan" />

    <AppLayout :breadcrumbs="breadcrumbs"
        description="Pastikan data Anda akurat dan terkini untuk pengalaman Aifarm yang optimal.">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="border-0">
                <CardContent class="pt-6">
                    <form>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <ImageUpload v-model:image="profileImage" @upload="handlePhotoUpload"
                                    @remove="handlePhotoRemove" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Upload your profile image here.
                                    Ensure the image is clear and recent.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div class="grid gap-2">
                                    <FloatingInput label="Email address" id="email" type="email"
                                        class="mt-1 block w-full" required autocomplete="username"
                                        placeholder="Email address" />
                                    <InputError class="mt-2" />
                                </div>

                                <div>
                                    <Input placeholder="" />
                                </div>
                            </div>
                        </div>

                        <MapInput :initialLatitude="51.505" :initialLongitude="-0.09" :zoom="13"
                            @update:coordinates="handleCoordinateUpdate" />

                        <div v-if="selectedCoordinates" class="mt-4 p-4 bg-muted rounded-md">
                            <h2 class="font-semibold mb-2">Selected Location:</h2>
                            <p>Latitude: {{ selectedCoordinates.latitude }}</p>
                            <p>Longitude: {{ selectedCoordinates.longitude }}</p>
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-between px-6 pb-6">
                    <Button variant="outline">
                        Cancel
                    </Button>
                    <Button>Deploy</Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
