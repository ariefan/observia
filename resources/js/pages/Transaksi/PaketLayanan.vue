<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TransaksiSidebar from '@/components/TransaksiSidebar.vue';
import { Check, Layers, Crown, Zap, Building2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineOptions({
    layout: AppLayout,
});

interface Feature {
    name: string;
    description: string;
    included: boolean;
}

interface Package {
    id: string;
    slug: string;
    name: string;
    description: string;
    monthly_price: number;
    annual_price: number;
    annual_savings: number;
    features: Feature[];
    max_livestock: number | null;
    max_users: number | null;
    has_analytics: boolean;
    has_iot: boolean;
    has_expert_support: boolean;
    is_free: boolean;
    is_current: boolean;
    can_select: boolean;
    button_text: string;
    button_variant: string;
}

interface CurrentSubscription {
    id: string;
    plan_name: string;
    billing_cycle: 'monthly' | 'annual';
    price: number;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
    days_remaining: number | null;
    auto_renew: boolean;
}

interface Props {
    packages: Package[];
    currentSubscription: CurrentSubscription | null;
    pendingInvoices: number;
}

const props = defineProps<Props>();

const isAnnual = ref(true);
const selectedPackage = ref<Package | null>(null);
const showConfirmDialog = ref(false);
const isSubmitting = ref(false);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const getPrice = (pkg: Package) => {
    if (pkg.is_free) return 'Gratis';
    const price = isAnnual.value ? pkg.annual_price : pkg.monthly_price;
    return formatCurrency(price);
};

const getPricePerMonth = (pkg: Package) => {
    if (pkg.is_free) return null;
    if (isAnnual.value) {
        return formatCurrency(pkg.annual_price / 12);
    }
    return null;
};

const getPackageIcon = (slug: string) => {
    switch (slug) {
        case 'starter':
            return Zap;
        case 'pro':
            return Crown;
        case 'enterprise':
            return Building2;
        default:
            return Zap;
    }
};

const featureMatrix = computed(() => {
    if (!props.packages.length) return [];

    // Get all unique feature names from the first package (they should all have the same features)
    const allFeatures = props.packages[0]?.features || [];

    return allFeatures.map(feature => ({
        name: feature.name,
        starter: props.packages.find(p => p.slug === 'starter')?.features.find(f => f.name === feature.name)?.included || false,
        pro: props.packages.find(p => p.slug === 'pro')?.features.find(f => f.name === feature.name)?.included || false,
        enterprise: props.packages.find(p => p.slug === 'enterprise')?.features.find(f => f.name === feature.name)?.included || false,
    }));
});

const handlePackageSelect = (pkg: Package) => {
    if (pkg.is_current || !pkg.can_select) return;
    selectedPackage.value = pkg;
    showConfirmDialog.value = true;
};

const confirmSubscribe = () => {
    if (!selectedPackage.value) return;

    isSubmitting.value = true;

    router.post('/transaksi/subscribe', {
        plan_id: selectedPackage.value.id,
        billing_cycle: isAnnual.value ? 'annual' : 'monthly',
    }, {
        onFinish: () => {
            isSubmitting.value = false;
            showConfirmDialog.value = false;
            selectedPackage.value = null;
        },
    });
};

const toggleAutoRenew = () => {
    router.post('/transaksi/toggle-auto-renew', {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="flex flex-col lg:flex-row">
        <div class="lg:block">
            <TransaksiSidebar current-route="transaksi.paket-layanan" :pending-count="pendingInvoices" />
        </div>

        <div class="flex-1 p-3 sm:p-4 lg:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Current Subscription Status -->
                <div v-if="currentSubscription" class="mb-6 rounded-xl border border-teal-200 dark:border-teal-800 bg-teal-50 dark:bg-teal-950/50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-teal-900 dark:text-teal-100">Langganan Aktif: {{ currentSubscription.plan_name }}</h3>
                                <Badge variant="outline" class="border-teal-600 text-teal-600 dark:border-teal-400 dark:text-teal-400">
                                    {{ currentSubscription.billing_cycle === 'annual' ? 'Tahunan' : 'Bulanan' }}
                                </Badge>
                            </div>
                            <p class="mt-1 text-sm text-teal-700 dark:text-teal-300">
                                <template v-if="currentSubscription.days_remaining !== null">
                                    Berlaku hingga {{ currentSubscription.days_remaining }} hari lagi
                                </template>
                                <template v-else>
                                    Paket gratis tanpa batas waktu
                                </template>
                            </p>
                        </div>
                        <div v-if="!currentSubscription.plan_name.toLowerCase().includes('starter')" class="flex items-center gap-3">
                            <span class="text-sm text-teal-700 dark:text-teal-300">Perpanjang otomatis</span>
                            <Switch
                                :checked="currentSubscription.auto_renew"
                                @update:checked="toggleAutoRenew"
                            />
                        </div>
                    </div>
                </div>

                <!-- Header with gradient card -->
                <section class="relative">
                    <div class="absolute inset-0 -z-10">
                        <div class="mx-auto h-64 max-w-5xl rounded-3xl bg-gradient-to-r from-teal-200/70 via-teal-100 to-cyan-100 dark:from-teal-900/50 dark:via-teal-800/30 dark:to-cyan-900/30"></div>
                    </div>

                    <div class="px-4 pt-2 sm:px-6">
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100 sm:text-3xl">
                            <span class="font-semibold text-teal-600 dark:text-teal-400">Produktivitas</span> Peternakan Meningkat dengan Solusi Aifarm
                        </h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                            Pilih paket Aifarm yang tepat untuk memaksimalkan keuntungan peternakan Anda.
                        </p>

                        <!-- Billing Toggle -->
                        <div class="mt-4 flex items-center gap-3">
                            <span :class="['text-sm font-medium', !isAnnual ? 'text-teal-600 dark:text-teal-400' : 'text-slate-500 dark:text-slate-400']">Bulanan</span>
                            <Switch v-model:checked="isAnnual" />
                            <span :class="['text-sm font-medium', isAnnual ? 'text-teal-600 dark:text-teal-400' : 'text-slate-500 dark:text-slate-400']">
                                Tahunan
                                <Badge v-if="isAnnual" variant="secondary" class="ml-1 bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300">
                                    Hemat 20%
                                </Badge>
                            </span>
                        </div>
                    </div>

                    <!-- Pricing Card -->
                    <div class="mt-6">
                        <div class="mx-auto max-w-6xl overflow-hidden rounded-3xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-slate-100 dark:ring-slate-800">
                            <!-- Columns header -->
                            <div class="grid grid-cols-4 text-center">
                                <div class="py-4 lg:py-6"></div>

                                <div
                                    v-for="pkg in packages"
                                    :key="pkg.id"
                                    class="py-4 lg:py-6 px-1 relative"
                                    :class="{ 'bg-teal-50 dark:bg-teal-950/50': pkg.slug === 'pro' }"
                                >
                                    <div v-if="pkg.slug === 'pro'" class="absolute inset-2 sm:inset-4 lg:inset-6 rounded-lg lg:rounded-2xl border border-teal-200 dark:border-teal-800 -z-10"></div>

                                    <div class="flex items-center justify-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                        <component :is="getPackageIcon(pkg.slug)" class="h-3 w-3" />
                                        {{ pkg.name }}
                                    </div>

                                    <div :class="['text-lg sm:text-xl lg:text-2xl font-bold text-slate-900 dark:text-slate-100', pkg.slug === 'pro' ? 'font-extrabold' : '']">
                                        <template v-if="pkg.is_free">Gratis</template>
                                        <template v-else>
                                            <span class="hidden sm:inline">{{ getPrice(pkg) }}</span>
                                            <span class="sm:hidden">{{ formatCurrency(isAnnual ? pkg.annual_price / 1000 : pkg.monthly_price / 1000) }}K</span>
                                        </template>
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                        <template v-if="!pkg.is_free">
                                            /{{ isAnnual ? 'Tahun' : 'Bulan' }}/Peternakan
                                        </template>
                                        <template v-else>
                                            Selamanya
                                        </template>
                                    </div>

                                    <div v-if="getPricePerMonth(pkg)" class="mt-1 text-xs text-teal-600 dark:text-teal-400">
                                        {{ getPricePerMonth(pkg) }}/bulan
                                    </div>

                                    <Button
                                        @click="handlePackageSelect(pkg)"
                                        :variant="pkg.is_current ? 'secondary' : (pkg.slug === 'pro' ? 'default' : 'outline')"
                                        :disabled="pkg.is_current"
                                        size="sm"
                                        class="mt-2 lg:mt-3"
                                        :class="{ 'bg-teal-600 hover:bg-teal-700': pkg.slug === 'pro' && !pkg.is_current }"
                                    >
                                        {{ pkg.button_text }}
                                    </Button>
                                </div>
                            </div>

                            <!-- Feature rows -->
                            <div class="border-t border-slate-100 dark:border-slate-800"></div>
                            <div class="p-3 sm:p-4 lg:p-6">
                                <div class="space-y-2 lg:space-y-3">
                                    <!-- row helper -->
                                    <div class="grid grid-cols-4 items-center gap-1 sm:gap-2 lg:gap-3 text-xs sm:text-sm">
                                        <div class="font-medium text-slate-600 dark:text-slate-300">Fitur</div>
                                        <div v-for="pkg in packages" :key="pkg.slug" class="text-center text-slate-500 dark:text-slate-400">
                                            {{ pkg.name }}
                                        </div>
                                    </div>

                                    <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

                                    <!-- Feature rows -->
                                    <div
                                        v-for="(feature, index) in featureMatrix"
                                        :key="feature.name"
                                        class="grid grid-cols-4 items-center gap-1 sm:gap-2 lg:gap-3"
                                    >
                                        <div
                                            :class="[
                                                'rounded-lg lg:rounded-xl px-2 sm:px-3 lg:px-4 py-2 lg:py-3 text-xs sm:text-sm text-slate-700 dark:text-slate-300',
                                                index % 2 === 0 ? 'bg-teal-50/60 dark:bg-teal-950/30' : 'bg-white dark:bg-transparent'
                                            ]"
                                        >
                                            {{ feature.name }}
                                        </div>
                                        <div class="text-center">
                                            <Check
                                                v-if="feature.starter"
                                                class="mx-auto h-4 w-4 lg:h-5 lg:w-5 text-teal-600 dark:text-teal-400"
                                            />
                                            <span
                                                v-else
                                                class="inline-block h-4 w-4 lg:h-5 lg:w-5 rounded border border-slate-300 dark:border-slate-600 align-middle"
                                            ></span>
                                        </div>
                                        <div class="text-center">
                                            <Check
                                                v-if="feature.pro"
                                                class="mx-auto h-4 w-4 lg:h-5 lg:w-5 text-teal-600 dark:text-teal-400"
                                            />
                                            <span
                                                v-else
                                                class="inline-block h-4 w-4 lg:h-5 lg:w-5 rounded border border-slate-300 dark:border-slate-600 align-middle"
                                            ></span>
                                        </div>
                                        <div class="text-center">
                                            <Check
                                                v-if="feature.enterprise"
                                                class="mx-auto h-4 w-4 lg:h-5 lg:w-5 text-teal-600 dark:text-teal-400"
                                            />
                                            <span
                                                v-else
                                                class="inline-block h-4 w-4 lg:h-5 lg:w-5 rounded border border-slate-300 dark:border-slate-600 align-middle"
                                            ></span>
                                        </div>
                                    </div>

                                    <!-- Limits row -->
                                    <div class="h-px bg-slate-100 dark:bg-slate-800 mt-4"></div>
                                    <div class="grid grid-cols-4 items-center gap-1 sm:gap-2 lg:gap-3">
                                        <div class="rounded-lg lg:rounded-xl px-2 sm:px-3 lg:px-4 py-2 lg:py-3 text-xs sm:text-sm text-slate-700 dark:text-slate-300 bg-teal-50/60 dark:bg-teal-950/30">
                                            Maksimal Ternak
                                        </div>
                                        <div v-for="pkg in packages" :key="pkg.slug + '-livestock'" class="text-center text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ pkg.max_livestock || 'Unlimited' }}
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 items-center gap-1 sm:gap-2 lg:gap-3">
                                        <div class="rounded-lg lg:rounded-xl px-2 sm:px-3 lg:px-4 py-2 lg:py-3 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                                            Maksimal Pengguna
                                        </div>
                                        <div v-for="pkg in packages" :key="pkg.slug + '-users'" class="text-center text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ pkg.max_users || 'Unlimited' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA banner -->
                    <div class="mx-auto mt-8 max-w-5xl">
                        <div class="overflow-hidden rounded-3xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-100 dark:ring-slate-800">
                            <div class="grid grid-cols-1 items-center gap-4 p-6 md:grid-cols-[1fr_auto]">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="absolute -left-2 -top-2 size-3 rounded-full bg-teal-300 dark:bg-teal-600"></div>
                                        <Layers class="h-12 w-12 text-slate-700 dark:text-slate-300" stroke-width="1.5" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-slate-100">Daftar ke paket Aifarm yang tepat untuk Anda.</div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                            Hubungi tim ahli kami untuk mendapatkan bantuan memilih paket yang sesuai.
                                        </p>
                                    </div>
                                </div>
                                <div class="md:justify-self-end">
                                    <Button class="rounded-full bg-teal-600 hover:bg-teal-700">
                                        Butuh bantuan
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Confirmation Dialog -->
    <Dialog v-model:open="showConfirmDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Konfirmasi Langganan</DialogTitle>
                <DialogDescription>
                    Anda akan berlangganan paket berikut:
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedPackage" class="py-4">
                <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                    <div class="flex items-center gap-3">
                        <component :is="getPackageIcon(selectedPackage.slug)" class="h-8 w-8 text-teal-600 dark:text-teal-400" />
                        <div>
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100">{{ selectedPackage.name }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ selectedPackage.description }}</p>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-slate-100 dark:border-slate-700 pt-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Periode</span>
                            <span class="font-medium dark:text-slate-200">{{ isAnnual ? 'Tahunan' : 'Bulanan' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-2">
                            <span class="text-slate-600 dark:text-slate-400">Harga</span>
                            <span class="font-medium dark:text-slate-200">{{ getPrice(selectedPackage) }}</span>
                        </div>
                        <div v-if="isAnnual && !selectedPackage.is_free" class="flex justify-between text-sm mt-2 text-teal-600 dark:text-teal-400">
                            <span>Hemat</span>
                            <span class="font-medium">{{ formatCurrency(selectedPackage.monthly_price * 12 - selectedPackage.annual_price) }}</span>
                        </div>
                    </div>
                </div>

                <p v-if="!selectedPackage.is_free" class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    Tagihan akan dibuat dan Anda akan diarahkan ke halaman pembayaran.
                </p>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="showConfirmDialog = false" :disabled="isSubmitting">
                    Batal
                </Button>
                <Button @click="confirmSubscribe" :disabled="isSubmitting" class="bg-teal-600 hover:bg-teal-700">
                    {{ isSubmitting ? 'Memproses...' : 'Konfirmasi' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
