<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card'
import { Avatar, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Venus, Mars, Plus } from 'lucide-vue-next';
import LivestockDefault from "@/assets/livestock-default.png";

defineProps({
    herd: Object,
    livestocks: Array,
});
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
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div>
                                <h1 class="text-lg font-semibold text-primary">{{ herd.name }}</h1>
                                <p class="text-sm">{{ herd.description }}</p>
                            </div>
                        </div>
                        <!-- <div class="text-sm text-right">
                            <div>Masuk Kandang: <span class="font-medium">1 Juni 2024</span></div>
                            <div>Keluar Kandang: -</div>
                        </div> -->
                    </div>

                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between items-center">
                                <h2 class="font-semibold flex">
                                    <Mars class="mr-2 text-cyan-500" /> Jantan
                                </h2>
                            </div>

                            <div class="space-y-2 mt-2">
                                <Card v-for="(goat, i) in livestocks.filter(l => l.sex === 'M')" :key="goat.id">
                                    <CardContent class="flex items-start gap-4 p-4">
                                        <Avatar size="base">
                                            <AvatarImage :src="goat.photo?.[0] || LivestockDefault" alt="Jantan" />
                                        </Avatar>
                                        <div class="flex-1">
                                            <div class="text-sm text-muted-foreground"></div>
                                            <div class="text-sm mt-2 grid grid-cols-4 gap-x-4">
                                                <div>
                                                    <span>
                                                        <Badge>{{ goat.aifarm_id }}</Badge>
                                                        <div class="font-semibold">{{ goat.name }}</div>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p>Kelahiran</p>
                                                    <strong>{{ goat.birthdate || '-' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center">
                                <h2 class="font-semibold flex">
                                    <Venus class="mr-2 text-pink-500" /> Betina
                                </h2>
                            </div>

                            <div class="space-y-2 mt-2">
                                <Card v-for="(goat, i) in livestocks.filter(l => l.sex === 'F')" :key="goat.id">
                                    <CardContent class="flex items-start gap-4 p-4">
                                        <Avatar size="base">
                                            <AvatarImage :src="goat.photo?.[0] || LivestockDefault" alt="Betina" />
                                        </Avatar>
                                        <div class="flex-1">
                                            <div class="text-sm text-muted-foreground"></div>
                                            <div class="text-sm mt-2 grid grid-cols-4 gap-x-4">
                                                <div>
                                                    <span>
                                                        <Badge>{{ goat.aifarm_id }}</Badge>
                                                        <div class="font-semibold">{{ goat.name }}</div>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p>Kelahiran</p>
                                                    <strong>{{ goat.birthdate || '-' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
