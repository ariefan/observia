<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    RefreshCw,
    Filter,
    CheckCircle,
    XCircle,
    Clock,
    AlertCircle,
    CreditCard,
    Ban,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { watch } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface Invoice {
    id: string;
    invoice_number: string;
    farm_name: string;
    plan_name: string;
    subtotal: number;
    discount: number;
    total: number;
    status: string;
    due_date: string;
    paid_at: string | null;
    payment_method: string | null;
    is_overdue: boolean;
    paid_by: string | null;
    created_at: string;
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
}

interface Props {
    invoices: PaginatedData<Invoice>;
    filters: Filters;
}

const props = defineProps<Props>();

const statusFilter = ref(props.filters.status || '');
const showPaymentDialog = ref(false);
const showCancelDialog = ref(false);
const selectedInvoice = ref<Invoice | null>(null);

const form = useForm({
    payment_method: '',
    payment_reference: '',
    notes: '',
});

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

const getStatusConfig = (invoice: Invoice) => {
    if (invoice.is_overdue && invoice.status === 'pending') {
        return { label: 'Jatuh Tempo', variant: 'destructive' as const, icon: AlertCircle };
    }
    switch (invoice.status) {
        case 'pending':
            return { label: 'Pending', variant: 'warning' as const, icon: Clock };
        case 'paid':
            return { label: 'Lunas', variant: 'success' as const, icon: CheckCircle };
        case 'cancelled':
            return { label: 'Dibatalkan', variant: 'secondary' as const, icon: XCircle };
        case 'refunded':
            return { label: 'Refund', variant: 'outline' as const, icon: XCircle };
        default:
            return { label: invoice.status, variant: 'secondary' as const, icon: Clock };
    }
};

const applyFilters = () => {
    router.get(route('admin.billing.invoices'), {
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    statusFilter.value = '';
    router.get(route('admin.billing.invoices'), {}, {
        preserveState: true,
        replace: true,
    });
};

watch(statusFilter, () => {
    applyFilters();
});

const openPaymentDialog = (invoice: Invoice) => {
    selectedInvoice.value = invoice;
    form.reset();
    showPaymentDialog.value = true;
};

const openCancelDialog = (invoice: Invoice) => {
    selectedInvoice.value = invoice;
    showCancelDialog.value = true;
};

const submitPayment = () => {
    if (!selectedInvoice.value) return;
    form.post(route('admin.billing.invoices.mark-paid', selectedInvoice.value.id), {
        onSuccess: () => {
            showPaymentDialog.value = false;
            selectedInvoice.value = null;
        },
    });
};

const cancelInvoice = () => {
    if (!selectedInvoice.value) return;
    router.post(route('admin.billing.invoices.cancel', selectedInvoice.value.id), {}, {
        onSuccess: () => {
            showCancelDialog.value = false;
            selectedInvoice.value = null;
        },
    });
};

const paymentMethods = [
    { value: 'bank_transfer', label: 'Transfer Bank' },
    { value: 'e_wallet', label: 'E-Wallet' },
    { value: 'qris', label: 'QRIS' },
    { value: 'virtual_account', label: 'Virtual Account' },
    { value: 'cash', label: 'Tunai' },
    { value: 'other', label: 'Lainnya' },
];
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
                            Semua Invoice
                        </h1>
                        <p class="text-muted-foreground">
                            Kelola tagihan dan pembayaran
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
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="paid">Lunas</SelectItem>
                                    <SelectItem value="cancelled">Dibatalkan</SelectItem>
                                    <SelectItem value="refunded">Refund</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button
                            v-if="statusFilter"
                            variant="outline"
                            @click="clearFilters"
                        >
                            <RefreshCw class="h-4 w-4 mr-2" />
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoices Table -->
            <div class="bg-white dark:bg-slate-900 rounded-lg border dark:border-slate-800 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>No. Invoice</TableHead>
                            <TableHead>Farm</TableHead>
                            <TableHead class="hidden md:table-cell">Paket</TableHead>
                            <TableHead class="text-right">Total</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="hidden lg:table-cell">Jatuh Tempo</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="invoices.data.length === 0">
                            <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                Tidak ada invoice ditemukan
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="inv in invoices.data" :key="inv.id">
                            <TableCell class="font-mono text-sm">
                                {{ inv.invoice_number }}
                            </TableCell>
                            <TableCell>
                                <div class="font-medium">{{ inv.farm_name }}</div>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                {{ inv.plan_name }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="font-medium text-teal-600 dark:text-teal-400">
                                    {{ formatCurrency(inv.total) }}
                                </div>
                                <div v-if="inv.discount > 0" class="text-xs text-muted-foreground">
                                    Diskon {{ formatCurrency(inv.discount) }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="getStatusConfig(inv).variant"
                                    class="flex items-center gap-1 w-fit"
                                >
                                    <component :is="getStatusConfig(inv).icon" class="h-3 w-3" />
                                    {{ getStatusConfig(inv).label }}
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden lg:table-cell">
                                <div :class="inv.is_overdue && inv.status === 'pending' ? 'text-red-600 dark:text-red-400 font-medium' : ''">
                                    {{ formatDate(inv.due_date) }}
                                </div>
                                <div v-if="inv.paid_at" class="text-xs text-muted-foreground">
                                    Dibayar: {{ formatDate(inv.paid_at) }}
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        v-if="inv.status === 'pending'"
                                        size="sm"
                                        variant="outline"
                                        class="text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-950"
                                        @click="openPaymentDialog(inv)"
                                        title="Tandai Lunas"
                                    >
                                        <CreditCard class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-if="inv.status === 'pending'"
                                        size="sm"
                                        variant="outline"
                                        class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950"
                                        @click="openCancelDialog(inv)"
                                        title="Batalkan"
                                    >
                                        <Ban class="h-4 w-4" />
                                    </Button>
                                    <span v-if="inv.status !== 'pending'" class="text-sm text-muted-foreground">
                                        {{ inv.payment_method || '-' }}
                                    </span>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div v-if="invoices.links && invoices.links.length > 3" class="border-t dark:border-slate-800 px-4 py-3 flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        Halaman {{ invoices.meta?.current_page }} dari {{ invoices.meta?.last_page }}
                    </div>
                    <div class="flex gap-1">
                        <template v-for="link in invoices.links" :key="link.label">
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

    <!-- Mark as Paid Dialog -->
    <Dialog v-model:open="showPaymentDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Tandai Invoice Lunas</DialogTitle>
                <DialogDescription>
                    Konfirmasi pembayaran untuk invoice {{ selectedInvoice?.invoice_number }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitPayment" class="space-y-4">
                <!-- Invoice Summary -->
                <div v-if="selectedInvoice" class="rounded-lg border dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Farm</span>
                        <span class="font-medium">{{ selectedInvoice.farm_name }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-muted-foreground">Total</span>
                        <span class="font-bold text-teal-600 dark:text-teal-400">
                            {{ formatCurrency(selectedInvoice.total) }}
                        </span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="space-y-2">
                    <Label>Metode Pembayaran</Label>
                    <Select v-model="form.payment_method">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih metode pembayaran" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="method in paymentMethods"
                                :key="method.value"
                                :value="method.value"
                            >
                                {{ method.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.payment_method" class="text-sm text-red-500">
                        {{ form.errors.payment_method }}
                    </p>
                </div>

                <!-- Payment Reference -->
                <div class="space-y-2">
                    <Label>Nomor Referensi (Opsional)</Label>
                    <Input
                        v-model="form.payment_reference"
                        placeholder="Contoh: nomor transfer"
                    />
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <Label>Catatan (Opsional)</Label>
                    <Textarea
                        v-model="form.notes"
                        placeholder="Catatan tambahan..."
                        rows="2"
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showPaymentDialog = false">
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing || !form.payment_method"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        <CheckCircle class="h-4 w-4 mr-2" />
                        {{ form.processing ? 'Memproses...' : 'Tandai Lunas' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Cancel Invoice Dialog -->
    <AlertDialog v-model:open="showCancelDialog">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Batalkan Invoice?</AlertDialogTitle>
                <AlertDialogDescription>
                    Apakah Anda yakin ingin membatalkan invoice {{ selectedInvoice?.invoice_number }}?
                    Tindakan ini tidak dapat dibatalkan.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Batal</AlertDialogCancel>
                <AlertDialogAction
                    @click="cancelInvoice"
                    class="bg-red-600 hover:bg-red-700 text-white"
                >
                    Ya, Batalkan Invoice
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
