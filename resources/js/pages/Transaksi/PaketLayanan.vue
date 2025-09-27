<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TransaksiSidebar from '@/components/TransaksiSidebar.vue';
import { Check } from 'lucide-vue-next';
import { Layers } from 'lucide-vue-next';

defineOptions({
    layout: AppLayout,
});

interface Feature {
    name: string;
    starter: boolean;
    pro: boolean;
    enterprise: boolean;
}

interface Props {
    packages: Array<{
        id: string;
        name: string;
        price: string;
        price_numeric: number;
        period: string;
        description?: string;
        features: Array<{
            name: string;
            included: boolean;
        }>;
        button_text: string;
        button_variant: string;
        is_current: boolean;
    }>;
}

const props = defineProps<Props>();

// Convert package data to feature matrix format
const features: Feature[] = [
    {
        name: 'Kelola kesehatan dan produksi ternak',
        starter: true,
        pro: true,
        enterprise: true,
    },
    {
        name: 'Lacak data ternak untuk pengambilan keputusan',
        starter: true,
        pro: true,
        enterprise: true,
    },
    {
        name: 'Stud book dan sertifikat per ternak',
        starter: true,
        pro: true,
        enterprise: true,
    },
    {
        name: 'Visualisasi data produktivitas susu dan bobot ternak',
        starter: false,
        pro: true,
        enterprise: true,
    },
    {
        name: 'Rangking produktivitas susu dan bobot ternak',
        starter: false,
        pro: true,
        enterprise: true,
    },
    {
        name: 'Dukungan tim ahli Aifarm',
        starter: false,
        pro: true,
        enterprise: true,
    },
    {
        name: 'Solusi IoT',
        starter: false,
        pro: false,
        enterprise: true,
    },
];

const handlePackageSelect = (packageId: string) => {
    console.log('Selected package:', packageId);
};
</script>

<template>
    <div class="flex flex-col lg:flex-row">
        <div class="lg:block">
            <TransaksiSidebar current-route="transaksi.paket-layanan" />
        </div>

        <div class="flex-1 p-3 sm:p-4 lg:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header with gradient card -->
                <section class="relative">
                    <div class="absolute inset-0 -z-10">
                        <div class="mx-auto h-64 max-w-5xl rounded-3xl bg-gradient-to-r from-teal-200/70 via-teal-100 to-cyan-100"></div>
                    </div>

                    <div class="px-4 pt-2 sm:px-6">
                        <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">
                            <span class="font-semibold text-teal-600">Produktivitas</span> Peternakan Meningkat dengan Solusi Aifarm
                        </h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-500">
                            Pilih paket Aifarm yang tepat untuk memaksimalkan keuntungan peternakan Anda.
                        </p>
                    </div>

                    <!-- Pricing Card -->
                    <div class="mt-6">
                        <div class="mx-auto max-w-6xl overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-slate-100">
                            <!-- Columns header -->
                            <div class="grid grid-cols-4 text-center">
                                <div class="py-4 lg:py-6"></div>
                                <!-- Starter -->
                                <div class="py-4 lg:py-6 px-1">
                                    <div class="text-xs text-slate-500">Starter</div>
                                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-slate-900">Free</div>
                                    <div class="mt-1 text-xs text-slate-400 hidden sm:block">/Tahun/Peternakan</div>
                                    <div class="mt-1 text-xs text-slate-400 sm:hidden">/Tahun</div>
                                    <button
                                        @click="handlePackageSelect('starter')"
                                        class="mt-2 lg:mt-3 inline-flex items-center justify-center rounded-full border border-slate-200 px-2 sm:px-3 lg:px-4 py-1 lg:py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    >
                                        <span class="hidden sm:inline">Mulai coba</span>
                                        <span class="sm:hidden">Coba</span>
                                    </button>
                                </div>

                                <!-- Pro (highlight) -->
                                <div class="py-4 lg:py-6 px-1 relative">
                                    <div class="absolute inset-2 sm:inset-4 lg:inset-6 rounded-lg lg:rounded-2xl border border-teal-200 bg-teal-50 -z-10"></div>
                                    <div class="text-xs text-slate-500">Pro</div>
                                    <div class="text-lg sm:text-xl lg:text-2xl font-extrabold text-slate-900">
                                        <span class="hidden sm:inline">Rp600,000</span>
                                        <span class="sm:hidden">600K</span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-400 hidden sm:block">/Tahun/Peternakan</div>
                                    <div class="mt-1 text-xs text-slate-400 sm:hidden">/Tahun</div>
                                    <button
                                        @click="handlePackageSelect('pro')"
                                        class="mt-2 lg:mt-3 inline-flex items-center justify-center rounded-full bg-teal-600 px-2 sm:px-3 lg:px-4 py-1 lg:py-1.5 text-xs font-semibold text-white hover:bg-teal-700"
                                    >
                                        <span class="hidden lg:inline">Coba gratis 7 hari</span>
                                        <span class="lg:hidden">Gratis 7 hari</span>
                                    </button>
                                </div>

                                <!-- Enterprise -->
                                <div class="py-4 lg:py-6 px-1">
                                    <div class="text-xs text-slate-500">Enterprise</div>
                                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-slate-900">
                                        <span class="hidden sm:inline">Rp1,000,000</span>
                                        <span class="sm:hidden">1M</span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-400 hidden sm:block">/Tahun/Peternakan</div>
                                    <div class="mt-1 text-xs text-slate-400 sm:hidden">/Tahun</div>
                                    <button
                                        @click="handlePackageSelect('enterprise')"
                                        class="mt-2 lg:mt-3 inline-flex items-center justify-center rounded-full border border-slate-200 px-2 sm:px-3 lg:px-4 py-1 lg:py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    >
                                        Upgrade
                                    </button>
                                </div>
                            </div>

                            <!-- Feature rows -->
                            <div class="border-t border-slate-100"></div>
                            <div class="p-3 sm:p-4 lg:p-6">
                                <div class="space-y-2 lg:space-y-3">
                                    <!-- row helper -->
                                    <div class="grid grid-cols-4 items-center gap-1 sm:gap-2 lg:gap-3 text-xs sm:text-sm">
                                        <div class="font-medium text-slate-600">Fitur</div>
                                        <div class="text-center text-slate-500">Free</div>
                                        <div class="text-center text-slate-500">Pro</div>
                                        <div class="text-center text-slate-500">
                                            <span class="hidden sm:inline">Ent</span>
                                            <span class="sm:hidden">E</span>
                                        </div>
                                    </div>

                                    <div class="h-px bg-slate-100"></div>

                                    <!-- Feature rows -->
                                    <div
                                        v-for="(feature, index) in features"
                                        :key="feature.name"
                                        class="grid grid-cols-4 items-center gap-1 sm:gap-2 lg:gap-3"
                                    >
                                        <div
                                            :class="[
                                                'rounded-lg lg:rounded-xl px-2 sm:px-3 lg:px-4 py-2 lg:py-3 text-xs sm:text-sm text-slate-700',
                                                index % 2 === 0 ? 'bg-teal-50/60' : 'bg-white'
                                            ]"
                                        >
                                            {{ feature.name }}
                                        </div>
                                        <div class="text-center">
                                            <Check
                                                v-if="feature.starter"
                                                class="mx-auto h-4 w-4 lg:h-5 lg:w-5 text-teal-600"
                                            />
                                            <span
                                                v-else
                                                class="inline-block h-4 w-4 lg:h-5 lg:w-5 rounded border border-slate-300 align-middle"
                                            ></span>
                                        </div>
                                        <div class="text-center">
                                            <Check
                                                v-if="feature.pro"
                                                class="mx-auto h-4 w-4 lg:h-5 lg:w-5 text-teal-600"
                                            />
                                            <span
                                                v-else
                                                class="inline-block h-4 w-4 lg:h-5 lg:w-5 rounded border border-slate-300 align-middle"
                                            ></span>
                                        </div>
                                        <div class="text-center">
                                            <Check
                                                v-if="feature.enterprise"
                                                class="mx-auto h-4 w-4 lg:h-5 lg:w-5 text-teal-600"
                                            />
                                            <span
                                                v-else
                                                class="inline-block h-4 w-4 lg:h-5 lg:w-5 rounded border border-slate-300 align-middle"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA banner -->
                    <div class="mx-auto mt-8 max-w-5xl">
                        <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                            <div class="grid grid-cols-1 items-center gap-4 p-6 md:grid-cols-[1fr_auto]">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="absolute -left-2 -top-2 size-3 rounded-full bg-teal-300"></div>
                                        <Layers class="h-12 w-12 text-slate-700" stroke-width="1.5" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">Daftar ke paket Aifarm yang tepat untuk Anda.</div>
                                        <p class="text-sm text-slate-500">
                                            Hubungi tim ahli kami untuk mendapatkan bantuan memilih paket yang sesuai.
                                        </p>
                                    </div>
                                </div>
                                <div class="md:justify-self-end">
                                    <button class="rounded-full bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                                        Butuh bantuan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>