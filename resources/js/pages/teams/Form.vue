<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { MapInput } from '@/components/ui/map-input';
import { ImageUpload } from '@/components/ui/image-upload';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';


defineProps<{
    name?: string;
}>();

const selectedCoordinates = ref<{ latitude: number; longitude: number } | null>(null);

function handleCoordinateUpdate(coordinates: { latitude: number; longitude: number }) {
    selectedCoordinates.value = coordinates;
    console.log('Coordinates updated:', coordinates);
}

const profileImage = ref(null);

const page = usePage();
const villages = computed(() => page.props.villages || []);

const handlePhotoUpload = ({ blob, dimensions }) => {
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

const back = () => window.history.back();

</script>

<template>

    <Head title="Peternakan" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
            <div>
                <h1 class="text-xl font-semibold text-primary">Peternakan</h1>
                <p class="text-sm">Pastikan data Anda akurat dan terkini untuk pengalaman Aifarm yang optimal.</p>
            </div>
            <Card class="border-0">
                <CardContent class="pt-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div>
                                <ImageUpload v-model:image="profileImage" @upload="handlePhotoUpload"
                                    @remove="handlePhotoRemove" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Upload your profile image here.
                                    Ensure the image is clear and recent.
                                </p>
                            </div>

                            <div>
                                <Label for="name">Nama Peternakan</Label>
                                <Input id="name" type="text" required v-model="form.name" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div>
                                <Label for="phone">Nomor Telepon Peternakan</Label>
                                <Input id="phone" type="text" required v-model="form.phone" />
                                <InputError :message="form.errors.phone" />
                            </div>

                            <div>
                                <Label for="email">Email Peternakan</Label>
                                <Input id="email" type="email" required v-model="form.email" />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div>
                                <Label for="owner">Pemilik Peternakan</Label>
                                <Input id="owner" type="text" required v-model="form.owner" />
                                <InputError :message="form.errors.owner" />
                            </div>

                            <div>
                                <Label for="village_id">Kecamatan</Label>
                                <Input id="village_id" type="text" list="villages" required v-model="form.village_id" />
                                <datalist id="villages">
                                    <option v-for="village in villages" :key="village.id" :value="village.id">
                                        {{ village.name }}
                                    </option>
                                </datalist>
                                <InputError :message="form.errors.village_id" />
                            </div>
                        </div>
                        <div>
                            <div>
                                <MapInput :initialLatitude="51.505" :initialLongitude="-0.09" :zoom="13"
                                    @update:coordinates="handleCoordinateUpdate" />
                            </div>
                        </div>
                    </div>


                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-between px-6 pb-6">
                    <Button variant="outline" @click="back">Batal</Button>
                    <Button>Simpan</Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
