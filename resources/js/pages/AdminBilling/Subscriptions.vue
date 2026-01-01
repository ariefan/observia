<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, RefreshCw, Search, Filter } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ref, watch } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface Subscription {
    id: string;
    farm_name: string;
    plan_name: string;
    billing_cycle: 'monthly' | 'annual';
    price: number;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
    days_remaining: number | null;
    auto_renew: boolean;
    created_by: string | null;
    created_at: string;
}

interface Plan {
    id: string;
    name: string;
}

interface PaginatedData<T> {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    meta?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

interface Filters {
    status?: string;
    plan_id?: string;
}

interface Props {
    subscriptions: PaginatedData<Subscription>;
    plans: Plan[];
    filters: Filters;
}

const props = defineProps<Props>();

const statusFilter = ref(props.filters.status || '');
const planFilter = ref(props.filters.plan_id || '');

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'active':
            return { label: 'Aktif', variant: 'success' as const };
        case 'pending':
            return { label: 'Pending', variant: 'warning' as const };
        case 'cancelled':
            return { label: 'Dibatalkan', variant: 'secondary' as const };
        case 'expired':
            return { label: 'Kadaluarsa', variant: 'destructive' as const };
        default:
            return { label: status, variant: 'secondary' as const };
    }
};

const applyFilters = () => {
    router.get(route('admin.billing.subscriptions'), {
        status: statusFilter.value || undefined,
        plan_id: planFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    statusFilter.value = '';
    planFilter.value = '';
    router.get(route('admin.billing.subscriptions'), {}, {
        preserveState: true,
        replace: true,
    });
};

watch([statusFilter, planFilter], () => {
    applyFilters();
});
</script>

<template>
    <div class="p-3 sm:p-4 lg:p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button as-child variant="ghost" size="icon">
                        <Link :href="route('admin.billing.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Semua Langganan
                        </h1>
                        <p class="text-muted-foreground">
                            Lihat dan kelola semua langganan farm
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardHeader class="pb-3">
                    <CardTitle class="text-base flex items-center gap-2">
                        <Filter class="h-4 w-4" />
                        Filter
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-4">
                        <div class="w-48">
                            <Select v-model="statusFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Semua Status</SelectItem>
                                    <SelectItem value="active">Aktif</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="cancelled">Dibatalkan</SelectItem>
                                    <SelectItem value="expired">Kadaluarsa</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="w-48">
                            <Select v-model="planFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Paket" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Semua Paket</SelectItem>
                                    <SelectItem v-for="plan in plans" :key="plan.id" :value="plan.id">
                                        {{ plan.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button
                            v-if="statusFilter || planFilter"
                            variant="outline"
                            @click="clearFilters"
                        >
                            <RefreshCw class="h-4 w-4 mr-2" />
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Subscriptions Table -->
            <div class="bg-white dark:bg-slate-900 rounded-lg border dark:border-slate-800 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Farm</TableHead>
                            <TableHead>Paket</TableHead>
                            <TableHead class="hidden md:table-cell">Periode</TableHead>
                            <TableHead class="text-right">Harga</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="hidden lg:table-cell">Berakhir</TableHead>
                            <TableHead class="hidden lg:table-cell">Auto-Renew</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="subscriptions.data.length === 0">
                            <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                Tidak ada langganan ditemukan
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="sub in subscriptions.data" :key="sub.id">
                            <TableCell>
                                <div class="font-medium">{{ sub.farm_name }}</div>
                                <div v-if="sub.created_by" class="text-xs text-muted-foreground">
                                    oleh {{ sub.created_by }}
                                </div>
                            </TableCell>
                            <TableCell>{{ sub.plan_name }}</TableCell>
                            <TableCell class="hidden md:table-cell">
                                <Badge variant="outline">
                                    {{ sub.billing_cycle === 'annual' ? 'Tahunan' : 'Bulanan' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right font-medium text-teal-600 dark:text-teal-400">
                                {{ formatCurrency(sub.price) }}
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusBadge(sub.status).variant">
                                    {{ getStatusBadge(sub.status).label }}
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden lg:table-cell">
                                <div>{{ formatDate(sub.ends_at) }}</div>
                                <div v-if="sub.days_remaining !== null && sub.days_remaining >= 0" class="text-xs text-muted-foreground">
                                    {{ sub.days_remaining }} hari lagi
                                </div>
                            </TableCell>
                            <TableCell class="hidden lg:table-cell">
                                <Badge :variant="sub.auto_renew ? 'success' : 'secondary'">
                                    {{ sub.auto_renew ? 'Ya' : 'Tidak' }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div v-if="subscriptions.links && subscriptions.links.length > 3" class="border-t dark:border-slate-800 px-4 py-3 flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        Halaman {{ subscriptions.meta?.current_page }} dari {{ subscriptions.meta?.last_page }}
                    </div>
                    <div class="flex gap-1">
                        <template v-for="link in subscriptions.links" :key="link.label">
                            <Button
                                v-if="link.url"
                                as-child
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                            >
                                <Link :href="link.url" v-html="link.label" />
                            </Button>
                            <Button
                                v-else
                                variant="outline"
                                size="sm"
                                disabled
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
