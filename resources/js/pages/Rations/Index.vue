<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, Building2, Pencil, Trash, Plus } from 'lucide-vue-next';

import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogClose
} from '@/components/ui/dialog';
import { Select } from '@/components/ui/select'
import { Input } from '@/components/ui/input'


import { ref, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    rations: Object,
    historyRations: Object,
    herdFeedings: Object,
    selectedMonth: String,
    selectedYear: String,
});

// Get current month and year
const now = new Date();
const currentMonth = String(now.getMonth() + 1).padStart(2, '0');
const currentYear = String(now.getFullYear());

const selectedMonth = ref(props.selectedMonth || currentMonth);
const selectedYear = ref(props.selectedYear || currentYear);

// Watch for changes and reload data
watch([selectedMonth, selectedYear], ([month, year]) => {
    router.get(route('rations.index'), { month, year }, { preserveState: true, preserveScroll: true });
});

onMounted(() => {
    // If no initial filter, trigger load with default
    if (!props.selectedMonth || !props.selectedYear) {
        router.get(route('rations.index'), { month: selectedMonth.value, year: selectedYear.value }, { preserveState: true, preserveScroll: true });
    }
});

const formatCurrency = (value) => {
    const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
    // Remove leading zero before the currency symbol if present (e.g., "Rp0" to "Rp")
    return formatted.replace(/^Rp0\b/, 'Rp');
};

const totalFeeds = (rationItems = []) => rationItems.length;

const totalCost = (rationItems = []) => {
    return rationItems.reduce((acc, item) => acc + (item.price), 0);
};



const months = [
    { value: '01', label: 'Januari' },
    { value: '02', label: 'Februari' },
    { value: '03', label: 'Maret' },
    { value: '04', label: 'April' },
    { value: '05', label: 'Mei' },
    { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' },
    { value: '08', label: 'Agustus' },
    { value: '09', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
]
</script>

<template>

    <Head title="Ransum" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Ransum
            </h2>
        </template>

        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
                <nav class="space-y-2">
                    <Link :href="route('rations.index')"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
                    Pakan
                    </Link>
                    <Link :href="route('herds.index')"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    Kandang
                    </Link>
                </nav>
            </aside>

            <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
                <Tabs default-value="ration" class="w-full">
                    <TabsList class="grid w-full grid-cols-6">
                        <TabsTrigger class="text-primary font-semibold" value="ration">
                            Stok Ransum
                        </TabsTrigger>
                        <TabsTrigger class="text-primary font-semibold" value="history">
                            Riwayat Ransum
                        </TabsTrigger>
                        <TabsTrigger class="text-primary font-semibold" value="feed">
                            Pakan
                        </TabsTrigger>
                    </TabsList>
                    <TabsContent value="ration">
                        <Card class="min-h-[600px]">
                            <CardHeader>
                                <CardTitle>Stok Ransum</CardTitle>
                                <CardDescription>
                                    Pastikan ketersediaan pakan selalu terjaga. Hindari kehabisan pakan yang
                                    dapat
                                    mengganggu kesehatan ternak dan produktivitas peternakan
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-2">
                                <div class="relative overflow-x-auto sm:rounded-lg">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Ransum</TableHead>
                                                <TableHead>Komposisi</TableHead>
                                                <TableHead>Jumlah</TableHead>
                                                <TableHead>Total Harga</TableHead>
                                                <TableHead class="text-right">
                                                    <Link :href="route('rations.create')">
                                                    <Button>Tambah Ransum</Button>
                                                    </Link>
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-for="ration in rations" :key="ration.id">
                                                <TableRow>
                                                    <TableCell
                                                        class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        {{ ration.name }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div v-if="ration.ration_items && ration.ration_items.length">
                                                            <ul class="ml-0">
                                                                <li v-for="item in ration.ration_items" :key="item.id"
                                                                    style="list-style: none;">
                                                                    {{ item.feed }},
                                                                    <!-- {{ formatCurrency(item.price) }} -->
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <span v-else>Tidak ada komposisi</span>
                                                    </TableCell>
                                                    <TableCell>
                                                        {{
                                                            ration.ration_items && ration.ration_items.length
                                                                ? ration.ration_items.reduce((sum, item) => sum +
                                                                    (item.quantity || 0), 0)
                                                                : 0
                                                        }} kg
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ formatCurrency(totalCost(ration.ration_items)) }}
                                                    </TableCell>
                                                    <TableCell class="text-right text-primary">
                                                        <Link :href="`${route('rations.edit', ration.id)}?restock=1`">
                                                        <Button variant="ghost">
                                                            <Plus class="w-4 h-4" /> Restock
                                                        </Button>
                                                        </Link>
                                                        <Link :href="route('rations.edit', ration.id)">
                                                        <Button variant="ghost" size="icon">
                                                            <Pencil class="w-4 h-4" />
                                                        </Button>
                                                        </Link>
                                                        <Dialog>
                                                            <DialogTrigger as-child>
                                                                <Button variant="ghost" size="icon">
                                                                    <Trash class="w-4 h-4" />
                                                                </Button>
                                                            </DialogTrigger>
                                                            <DialogContent>
                                                                <DialogHeader>
                                                                    <DialogTitle>Hapus Ransum?</DialogTitle>
                                                                    <div class="text-sm text-muted-foreground">
                                                                        Apakah Anda yakin ingin menghapus ransum
                                                                        ini? Tindakan ini tidak dapat dibatalkan.
                                                                    </div>
                                                                </DialogHeader>
                                                                <div class="flex justify-end gap-2">
                                                                    <DialogClose as-child>
                                                                        <Button variant="outline">Batal</Button>
                                                                    </DialogClose>
                                                                    <Link :href="route('rations.destroy', ration.id)"
                                                                        method="delete" as="button">
                                                                    <Button variant="destructive">Hapus</Button>
                                                                    </Link>
                                                                </div>
                                                            </DialogContent>
                                                        </Dialog>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <TableRow v-if="rations.length === 0">
                                                <TableCell colspan="5" class="px-6 py-4 text-center">
                                                    Belum ada data ransum.
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    <TabsContent value="history">
                        <Card class="min-h-[600px]">
                            <CardHeader>
                                <CardTitle>Riwayat Ransum</CardTitle>
                                <CardDescription>
                                    Selalu dapatkan informasi akurat tentang ketersediaan stok pakan ternak
                                    Anda, sehingga Anda dapat membuat keputusan pembelian yang tepat.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-2">
                                <div class="flex items-center gap-4 mb-4">
                                    <label>Bulan</label>
                                    <select
                                        class="w-32 rounded-md border border-gray-300 bg-white dark:bg-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                                        v-model="selectedMonth">
                                        <option v-for="month in months" :key="month.value" :value="month.value">
                                            {{ month.label }}
                                        </option>
                                    </select>
                                    <Input type="number" v-model="selectedYear" class="w-24" />
                                </div>
                                <div class="relative overflow-x-auto sm:rounded-lg">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Ransum</TableHead>
                                                <TableHead>Aksi</TableHead>
                                                <TableHead>Komposisi</TableHead>
                                                <TableHead>Jumlah</TableHead>
                                                <TableHead>Total Harga</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-for="ration in historyRations" :key="ration.id">
                                                <TableRow>
                                                    <TableCell
                                                        class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        {{ ration.name }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <span v-if="ration.action === 'restock'">Restock</span>
                                                        <span v-else-if="ration.action === 'create'">
                                                            Stok Baru
                                                        </span>
                                                        <span v-else-if="ration.action === 'update'">Update</span>
                                                        <span v-else>{{ ration.action }}</span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div
                                                            v-if="ration.history_ration_items && ration.history_ration_items.length">
                                                            <ul class="ml-0">
                                                                <li v-for="item in ration.history_ration_items"
                                                                    :key="item.id" style="list-style: none;">
                                                                    {{ item.feed }},
                                                                    <!-- {{ formatCurrency(item.price) }} -->
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <span v-else>Tidak ada komposisi</span>
                                                    </TableCell>
                                                    <TableCell>
                                                        {{
                                                            ration.history_ration_items &&
                                                                ration.history_ration_items.length
                                                                ? ration.history_ration_items.reduce((sum, item) => sum +
                                                                    (item.quantity || 0), 0)
                                                                : 0
                                                        }} kg
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ formatCurrency(totalCost(ration.history_ration_items)) }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <span v-if="ration.created_at">
                                                            {{ new Date(ration.created_at).toLocaleString('id-ID', {
                                                                dateStyle: 'long', timeStyle: 'short'
                                                            }) }}
                                                        </span>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <TableRow v-if="rations.length === 0">
                                                <TableCell colspan="5" class="px-6 py-4 text-center">
                                                    Belum ada data ransum.
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    <TabsContent value="feed">
                        <Card class="min-h-[600px]">
                            <CardHeader>
                                <CardTitle>
                                    <div class="flex items-center justify-between">
                                        Pakan
                                        <div>
                                            <Link :href="route('herds.feeding')">
                                            <Button size="sm">Catat pemberian pakan</Button>
                                            </Link>
                                        </div>
                                    </div>
                                </CardTitle>
                                <CardDescription>
                                    Pantau riwayat pemberian pakan pada setiap kandang untuk memastikan ternak mendapat
                                    nutrisi
                                    yang optimal dan konsisten setiap harinya.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-2">
                                <div class="flex items-center gap-4 mb-4">
                                    <label>Bulan</label>
                                    <select
                                        class="w-32 rounded-md border border-gray-300 bg-white dark:bg-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                                        v-model="selectedMonth">
                                        <option v-for="month in months" :key="month.value" :value="month.value">
                                            {{ month.label }}
                                        </option>
                                    </select>
                                    <Input type="number" v-model="selectedYear" class="w-24" />
                                </div>
                                <div class="relative overflow-x-auto sm:rounded-lg">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Nama Kandang</TableHead>
                                                <TableHead>Ransum</TableHead>
                                                <TableHead>Jumlah Pemberian</TableHead>
                                                <TableHead>Tanggal Pemberian</TableHead>
                                                <TableHead>Sesi</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-for="feeding in herdFeedings" :key="feeding.id">
                                                <TableRow>
                                                    <TableCell
                                                        class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        {{ feeding.herd ? feeding.herd.name : 'N/A' }}
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ feeding.ration ? feeding.ration.name : 'N/A' }}
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ feeding.quantity }} kg
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ new Date(feeding.date).toLocaleDateString('id-ID', {
                                                            dateStyle: 'medium'
                                                        }) }}
                                                        <span v-if="feeding.time" class="text-sm text-gray-500 ml-1">
                                                            {{ feeding.time }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <span v-if="feeding.session === 'morning'"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                                            Pagi
                                                        </span>
                                                        <span v-else-if="feeding.session === 'afternoon'"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-800 bg-orange-100 rounded-full">
                                                            Sore
                                                        </span>
                                                        <span v-else
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">
                                                            {{ feeding.session }}
                                                        </span>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <TableRow v-if="!herdFeedings || herdFeedings.length === 0">
                                                <TableCell colspan="5" class="px-6 py-4 text-center">
                                                    Belum ada data pemberian pakan.
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>
