<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    Eye,
    EyeOff,
    ArrowLeft,
    Check,
    X,
    Users,
    Sparkles,
    Wifi,
    HeadphonesIcon,
    BarChart3,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import {
    Card,
    CardContent,
    CardDescription,
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

defineOptions({
    layout: AppLayout,
});

interface Plan {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    monthly_price: number;
    annual_price: number;
    max_livestock: number | null;
    max_users: number | null;
    features: string[];
    has_analytics: boolean;
    has_iot: boolean;
    has_expert_support: boolean;
    is_active: boolean;
    is_visible: boolean;
    sort_order: number;
    subscriptions_count: number;
}

interface Props {
    plans: Plan[];
}

const props = defineProps<Props>();

const showPlanDialog = ref(false);
const showDeleteDialog = ref(false);
const editingPlan = ref<Plan | null>(null);
const deletingPlan = ref<Plan | null>(null);
const newFeature = ref('');

const form = useForm({
    name: '',
    slug: '',
    description: '',
    monthly_price: 0,
    annual_price: 0,
    max_livestock: null as number | null,
    max_users: null as number | null,
    features: [] as string[],
    has_analytics: false,
    has_iot: false,
    has_expert_support: false,
    is_active: true,
    is_visible: true,
    sort_order: 0,
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const generateSlug = (name: string) => {
    return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
};

const openCreateDialog = () => {
    editingPlan.value = null;
    form.reset();
    form.sort_order = props.plans.length;
    showPlanDialog.value = true;
};

const openEditDialog = (plan: Plan) => {
    editingPlan.value = plan;
    form.name = plan.name;
    form.slug = plan.slug;
    form.description = plan.description || '';
    form.monthly_price = plan.monthly_price;
    form.annual_price = plan.annual_price;
    form.max_livestock = plan.max_livestock;
    form.max_users = plan.max_users;
    form.features = [...plan.features];
    form.has_analytics = plan.has_analytics;
    form.has_iot = plan.has_iot;
    form.has_expert_support = plan.has_expert_support;
    form.is_active = plan.is_active;
    form.is_visible = plan.is_visible;
    form.sort_order = plan.sort_order;
    showPlanDialog.value = true;
};

const openDeleteDialog = (plan: Plan) => {
    deletingPlan.value = plan;
    showDeleteDialog.value = true;
};

const addFeature = () => {
    if (newFeature.value.trim()) {
        form.features.push(newFeature.value.trim());
        newFeature.value = '';
    }
};

const removeFeature = (index: number) => {
    form.features.splice(index, 1);
};

const submitPlan = () => {
    if (editingPlan.value) {
        form.put(route('admin.billing.plans.update', editingPlan.value.id), {
            onSuccess: () => {
                showPlanDialog.value = false;
                editingPlan.value = null;
            },
        });
    } else {
        form.post(route('admin.billing.plans.store'), {
            onSuccess: () => {
                showPlanDialog.value = false;
            },
        });
    }
};

const deletePlan = () => {
    if (!deletingPlan.value) return;
    router.delete(route('admin.billing.plans.destroy', deletingPlan.value.id), {
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingPlan.value = null;
        },
    });
};

const toggleVisibility = (plan: Plan) => {
    router.post(route('admin.billing.plans.toggle-visibility', plan.id));
};
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
                            Kelola Paket
                        </h1>
                        <p class="text-muted-foreground">
                            Tambah, edit, atau sembunyikan paket langganan
                        </p>
                    </div>
                </div>
                <Button @click="openCreateDialog">
                    <Plus class="h-4 w-4 mr-2" />
                    Tambah Paket
                </Button>
            </div>

            <!-- Plans Grid -->
            <div v-if="plans.length === 0" class="bg-white dark:bg-slate-900 rounded-lg border dark:border-slate-800 p-8 text-center">
                <Sparkles class="h-16 w-16 mx-auto mb-4 text-slate-300 dark:text-slate-600" />
                <h3 class="text-xl font-semibold mb-2 dark:text-slate-100">Belum ada paket</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Buat paket langganan pertama Anda.
                </p>
                <Button @click="openCreateDialog">
                    <Plus class="h-4 w-4 mr-2" />
                    Tambah Paket
                </Button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'relative',
                        !plan.is_visible && 'opacity-60',
                        !plan.is_active && 'border-red-200 dark:border-red-800'
                    ]"
                >
                    <!-- Visibility Badge -->
                    <div class="absolute top-4 right-4 flex gap-2">
                        <Badge v-if="!plan.is_visible" variant="secondary">
                            <EyeOff class="h-3 w-3 mr-1" />
                            Tersembunyi
                        </Badge>
                        <Badge v-if="!plan.is_active" variant="destructive">
                            Nonaktif
                        </Badge>
                    </div>

                    <CardHeader>
                        <CardTitle>{{ plan.name }}</CardTitle>
                        <CardDescription>{{ plan.slug }}</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Pricing -->
                        <div class="space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-muted-foreground">Bulanan:</span>
                                <span class="font-semibold text-teal-600 dark:text-teal-400">
                                    {{ plan.monthly_price === 0 ? 'Gratis' : formatCurrency(plan.monthly_price) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-muted-foreground">Tahunan:</span>
                                <span class="font-semibold text-teal-600 dark:text-teal-400">
                                    {{ plan.annual_price === 0 ? 'Gratis' : formatCurrency(plan.annual_price) }}
                                </span>
                            </div>
                        </div>

                        <!-- Limits -->
                        <div class="flex gap-4 text-sm">
                            <div class="flex items-center gap-1 text-muted-foreground">
                                <Users class="h-4 w-4" />
                                {{ plan.max_users ?? '∞' }} user
                            </div>
                            <div class="text-muted-foreground">
                                {{ plan.max_livestock ?? '∞' }} ternak
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="flex flex-wrap gap-2">
                            <Badge v-if="plan.has_analytics" variant="outline" class="text-xs">
                                <BarChart3 class="h-3 w-3 mr-1" />
                                Analytics
                            </Badge>
                            <Badge v-if="plan.has_iot" variant="outline" class="text-xs">
                                <Wifi class="h-3 w-3 mr-1" />
                                IoT
                            </Badge>
                            <Badge v-if="plan.has_expert_support" variant="outline" class="text-xs">
                                <HeadphonesIcon class="h-3 w-3 mr-1" />
                                Support
                            </Badge>
                        </div>

                        <!-- Active Subscriptions -->
                        <div class="pt-2 border-t dark:border-slate-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Langganan aktif:</span>
                                <Badge variant="secondary">{{ plan.subscriptions_count }}</Badge>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2">
                            <Button variant="outline" size="sm" class="flex-1" @click="openEditDialog(plan)">
                                <Pencil class="h-4 w-4 mr-1" />
                                Edit
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="toggleVisibility(plan)"
                                :title="plan.is_visible ? 'Sembunyikan' : 'Tampilkan'"
                            >
                                <Eye v-if="!plan.is_visible" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950"
                                @click="openDeleteDialog(plan)"
                                :disabled="plan.subscriptions_count > 0"
                                :title="plan.subscriptions_count > 0 ? 'Tidak bisa dihapus karena ada langganan aktif' : 'Hapus'"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

    <!-- Plan Dialog -->
    <Dialog v-model:open="showPlanDialog">
        <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ editingPlan ? 'Edit Paket' : 'Tambah Paket Baru' }}</DialogTitle>
                <DialogDescription>
                    {{ editingPlan ? 'Perbarui informasi paket langganan' : 'Buat paket langganan baru untuk pelanggan' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitPlan" class="space-y-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Paket</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            @input="form.slug = generateSlug(form.name)"
                            placeholder="Contoh: Pro"
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <Input id="slug" v-model="form.slug" placeholder="contoh: pro" />
                        <p v-if="form.errors.slug" class="text-sm text-red-500">{{ form.errors.slug }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Deskripsi</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Deskripsi singkat paket..."
                        rows="2"
                    />
                </div>

                <!-- Pricing -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="monthly_price">Harga Bulanan (IDR)</Label>
                        <Input
                            id="monthly_price"
                            v-model.number="form.monthly_price"
                            type="number"
                            min="0"
                            step="1000"
                        />
                        <p v-if="form.errors.monthly_price" class="text-sm text-red-500">{{ form.errors.monthly_price }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="annual_price">Harga Tahunan (IDR)</Label>
                        <Input
                            id="annual_price"
                            v-model.number="form.annual_price"
                            type="number"
                            min="0"
                            step="1000"
                        />
                        <p class="text-xs text-muted-foreground">
                            Diskon tahunan: {{ form.monthly_price > 0 ? Math.round((1 - form.annual_price / (form.monthly_price * 12)) * 100) : 0 }}%
                        </p>
                    </div>
                </div>

                <!-- Limits -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <Label for="max_livestock">Maks. Ternak</Label>
                        <Input
                            id="max_livestock"
                            v-model.number="form.max_livestock"
                            type="number"
                            min="1"
                            placeholder="Unlimited"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="max_users">Maks. User</Label>
                        <Input
                            id="max_users"
                            v-model.number="form.max_users"
                            type="number"
                            min="1"
                            placeholder="Unlimited"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="sort_order">Urutan</Label>
                        <Input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                        />
                    </div>
                </div>

                <!-- Premium Features -->
                <div class="space-y-4">
                    <Label>Fitur Premium</Label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="flex items-center justify-between p-3 rounded-lg border dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <BarChart3 class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">Analytics</span>
                            </div>
                            <Switch v-model:checked="form.has_analytics" />
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg border dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <Wifi class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">IoT</span>
                            </div>
                            <Switch v-model:checked="form.has_iot" />
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg border dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <HeadphonesIcon class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">Expert Support</span>
                            </div>
                            <Switch v-model:checked="form.has_expert_support" />
                        </div>
                    </div>
                </div>

                <!-- Custom Features List -->
                <div class="space-y-2">
                    <Label>Fitur Tambahan</Label>
                    <div class="flex gap-2">
                        <Input
                            v-model="newFeature"
                            placeholder="Tambah fitur..."
                            @keydown.enter.prevent="addFeature"
                        />
                        <Button type="button" variant="outline" @click="addFeature">
                            <Plus class="h-4 w-4" />
                        </Button>
                    </div>
                    <div v-if="form.features.length > 0" class="flex flex-wrap gap-2 mt-2">
                        <Badge
                            v-for="(feature, index) in form.features"
                            :key="index"
                            variant="secondary"
                            class="flex items-center gap-1"
                        >
                            <Check class="h-3 w-3 text-green-500" />
                            {{ feature }}
                            <button type="button" @click="removeFeature(index)" class="ml-1 hover:text-red-500">
                                <X class="h-3 w-3" />
                            </button>
                        </Badge>
                    </div>
                </div>

                <!-- Status -->
                <div class="flex gap-6">
                    <div class="flex items-center gap-2">
                        <Switch v-model:checked="form.is_active" id="is_active" />
                        <Label for="is_active">Paket Aktif</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <Switch v-model:checked="form.is_visible" id="is_visible" />
                        <Label for="is_visible">Tampilkan di Publik</Label>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showPlanDialog = false">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : (editingPlan ? 'Simpan Perubahan' : 'Buat Paket') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="showDeleteDialog">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Hapus Paket?</AlertDialogTitle>
                <AlertDialogDescription>
                    Apakah Anda yakin ingin menghapus paket "{{ deletingPlan?.name }}"?
                    Tindakan ini tidak dapat dibatalkan.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Batal</AlertDialogCancel>
                <AlertDialogAction
                    @click="deletePlan"
                    class="bg-red-600 hover:bg-red-700 text-white"
                >
                    Hapus
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
