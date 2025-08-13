<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card'
import { Avatar } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Venus, Mars, ArrowLeft, ImageOff } from 'lucide-vue-next';

defineProps({
    herd: Object,
    livestocks: Array,
});

const getPhotoUrl = (photoPath) => {
    return photoPath ? `/storage/${photoPath}` : '';
};
</script>

<template>

    <Head title="Kandang" />
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Kandang
            </h2>
        </template>

        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
                <nav class="space-y-2">
                    <Link :href="route('rations.index')"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    Pakan
                    </Link>
                    <Link :href="route('herds.index')"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
                    Kandang
                    </Link>
                </nav>
            </aside>

            <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
                <div class="p-4 space-y-6">
                    <div class="flex flex-col gap-2">
                        <div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <Link :href="route('herds.index')"
                                    class="inline-flex items-center gap-2 text-sm text-primary hover:underline">
                                <Button variant="ghost" size="icon">
                                    <ArrowLeft />
                                </Button>
                                </Link>
                                <h1 class="text-lg font-semibold text-primary">
                                    <span class="mb-2">{{ herd.name }}</span>
                                </h1>
                                <p class="text-sm">{{ herd.description }}</p>
                            </div>
                        </div>
                        <!-- <div class="text-sm text-right">
                            <div>Masuk Kandang: <span class="font-medium">1 Juni 2024</span></div>
                            <div>Keluar Kandang: -</div>
                        </div> -->
                    </div>

                    <div class="space-y-6">
                        <div v-for="sex in [{ key: 'M', label: 'Jantan', icon: Mars, color: 'text-cyan-500', alt: 'Jantan' }, { key: 'F', label: 'Betina', icon: Venus, color: 'text-pink-500', alt: 'Betina' }]"
                            :key="sex.key">
                            <div class="flex justify-between items-center">
                                <h2 class="font-semibold flex">
                                    <component :is="sex.icon" class="mr-2" :class="sex.color" /> {{ sex.label }}
                                </h2>
                            </div>
                            <div class="space-y-2 mt-2">
                                <template v-if="livestocks.filter(l => l.sex === sex.key).length">
                                    <Card v-for="l in livestocks.filter(l => l.sex === sex.key)" :key="l.id">
                                        <CardContent class="flex items-start gap-4 p-4">
                                            <Avatar size="base">
                                                <template v-if="l.photo?.[0]">
                                                    <AvatarImage :src="getPhotoUrl(l.photo[0])" :alt="sex.alt" />
                                                </template>
                                                <template v-else>
                                                    <ImageOff class="text-gray-400" />
                                                </template>
                                            </Avatar>
                                            <div class="flex-1">
                                                <div class="text-sm text-muted-foreground"></div>
                                                <div class="text-sm mt-2 grid grid-cols-4 gap-x-4">
                                                    <div>
                                                        <span>
                                                            <Badge>{{ l.tag_id }}</Badge>
                                                            <div class="font-semibold">{{ l.name }}</div>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p>Usia</p>
                                                        <strong>
                                                            {{ l.birthdate ? (Math.floor((new Date() - new
                                                                Date(l.birthdate)) / (365.25 * 24 * 60 * 60 * 1000))) +
                                                                ' th' : '-' }}
                                                        </strong>
                                                    </div>
                                                    <div>
                                                        <p>Bobot</p>
                                                        <strong>{{ l.weight ? l.weight + ' kg' : '-' }}</strong>
                                                    </div>
                                                    <div>
                                                        <p>Tgl Masuk Kandang</p>
                                                        <strong>{{ l.herd_entry_date ? new
                                                            Date(l.herd_entry_date).toLocaleDateString('id-ID', {
                                                                day:
                                                                    'numeric', month:
                                                                    'long', year: 'numeric'
                                                            }) : '-' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </template>
                                <template v-else>
                                    <div class="text-center text-gray-400 py-6">Tidak ada data</div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
