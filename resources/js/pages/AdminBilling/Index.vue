<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    TrendingUp,
    Users,
    CreditCard,
    AlertTriangle,
    ArrowRight,
    Package,
    Receipt,
    Clock,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

defineOptions({
    layout: AppLayout,
});

interface Subscription {
    id: string;
    farm_name: string;
    plan_name: string;
    billing_cycle: 'monthly' | 'annual';
    status: string;
    price: number;
    starts_at: string | null;
    ends_at: string | null;
    created_at: string;
}

interface Invoice {
    id: string;
    invoice_number: string;
    farm_name: string;
    plan_name: string;
    total: number;
    due_date: string;
    is_overdue: boolean;
}

interface PlanStat {
    name: string;
    active_count: number;
    monthly_price: number;
    annual_price: number;
}

interface Stats {
    total_subscriptions: number;
    active_subscriptions: number;
    pending_invoices: number;
    overdue_invoices: number;
    monthly_revenue: number;
    total_plans: number;
}

interface Props {
    stats: Stats;
    recentSubscriptions: Subscription[];
    pendingInvoices: Invoice[];
    planStats: PlanStat[];
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string) => {
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
</script>

<template>
    <div class="p-3 sm:p-4 lg:p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Manajemen Billing
                    </h1>
                    <p class="text-muted-foreground">
                        Kelola paket langganan, tagihan, dan pembayaran
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('admin.billing.invoices')">
                            <Receipt class="h-4 w-4 mr-2" />
                            Semua Invoice
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="route('admin.billing.plans')">
                            <Package class="h-4 w-4 mr-2" />
                            Kelola Paket
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Langganan</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_subscriptions }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.active_subscriptions }} aktif
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pendapatan Bulanan</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-teal-600 dark:text-teal-400">
                            {{ formatCurrency(stats.monthly_revenue) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Dari langganan aktif
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Invoice Pending</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pending_invoices }}</div>
                        <p class="text-xs text-muted-foreground">
                            Menunggu pembayaran
                        </p>
                    </CardContent>
                </Card>

                <Card :class="stats.overdue_invoices > 0 ? 'border-red-200 dark:border-red-800' : ''">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Invoice Jatuh Tempo</CardTitle>
                        <AlertTriangle class="h-4 w-4" :class="stats.overdue_invoices > 0 ? 'text-red-500' : 'text-muted-foreground'" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold" :class="stats.overdue_invoices > 0 ? 'text-red-600 dark:text-red-400' : ''">
                            {{ stats.overdue_invoices }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Perlu perhatian
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Subscriptions -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Langganan Terbaru</CardTitle>
                            <CardDescription>10 langganan terakhir</CardDescription>
                        </div>
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="route('admin.billing.subscriptions')">
                                Lihat Semua
                                <ArrowRight class="h-4 w-4 ml-1" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentSubscriptions.length === 0" class="text-center py-8 text-muted-foreground">
                            Belum ada langganan
                        </div>
                        <Table v-else>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Farm</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="sub in recentSubscriptions" :key="sub.id">
                                    <TableCell class="font-medium">{{ sub.farm_name }}</TableCell>
                                    <TableCell>
                                        <div>{{ sub.plan_name }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ sub.billing_cycle === 'annual' ? 'Tahunan' : 'Bulanan' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusBadge(sub.status).variant">
                                            {{ getStatusBadge(sub.status).label }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Pending Invoices -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Invoice Menunggu</CardTitle>
                            <CardDescription>Invoice yang perlu dibayar</CardDescription>
                        </div>
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="route('admin.billing.invoices')">
                                Lihat Semua
                                <ArrowRight class="h-4 w-4 ml-1" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="pendingInvoices.length === 0" class="text-center py-8 text-muted-foreground">
                            Tidak ada invoice pending
                        </div>
                        <Table v-else>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Jatuh Tempo</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="inv in pendingInvoices" :key="inv.id">
                                    <TableCell>
                                        <div class="font-mono text-sm">{{ inv.invoice_number }}</div>
                                        <div class="text-xs text-muted-foreground">{{ inv.farm_name }}</div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ formatCurrency(inv.total) }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Clock class="h-3 w-3" :class="inv.is_overdue ? 'text-red-500' : 'text-muted-foreground'" />
                                            <span :class="inv.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : ''">
                                                {{ formatDate(inv.due_date) }}
                                            </span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <!-- Plan Stats -->
            <Card class="mt-6">
                <CardHeader>
                    <CardTitle>Statistik Per Paket</CardTitle>
                    <CardDescription>Distribusi langganan aktif per paket</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="planStats.length === 0" class="text-center py-8 text-muted-foreground">
                        Belum ada paket
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="plan in planStats"
                            :key="plan.name"
                            class="p-4 rounded-lg border dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold dark:text-slate-100">{{ plan.name }}</h4>
                                <Badge variant="secondary">
                                    {{ plan.active_count }} aktif
                                </Badge>
                            </div>
                            <div class="text-sm text-muted-foreground space-y-1">
                                <div class="flex justify-between">
                                    <span>Bulanan:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">
                                        {{ formatCurrency(plan.monthly_price) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Tahunan:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">
                                        {{ formatCurrency(plan.annual_price) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
