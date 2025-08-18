<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Livestock {
  id: string;
  name: string;
  aifarm_id: string;
  tag_id: string;
  sex: 'M' | 'F';
  weight?: number;
  birthdate?: string;
  age_in_year?: number;
  age_in_month?: number;
  breed: {
    name: string;
    species: {
      name: string;
    };
  };
  herd?: {
    name: string;
  };
}

interface Props {
  livestocks: Livestock[];
  selectedLivestock?: Livestock | null;
  statusOptions: Record<string, string>;
}

const props = defineProps<Props>();

const form = useForm({
  livestock_id: props.selectedLivestock?.id || '',
  ending_date: new Date().toISOString().split('T')[0],
  ending_status: '',
  buyer_name: '',
  buyer_phone: '',
  buyer_email: '',
  price: '',
  receiving_farm_name: '',
  receiver_name: '',
  receiver_phone: '',
  receiver_email: '',
  loan_date: '',
  return_date: '',
  notes: '',
});

const selectedLivestock = ref<Livestock | null>(props.selectedLivestock || null);

const showBuyerFields = computed(() =>
  ['sold', 'gifted'].includes(form.ending_status)
);

const showPriceField = computed(() =>
  form.ending_status === 'sold'
);

const showLoanFields = computed(() =>
  form.ending_status === 'loaned'
);

// Watch for livestock selection changes
watch(selectedLivestock, (newLivestock) => {
  form.livestock_id = newLivestock?.id || '';
});

// Clear conditional fields when status changes
watch(() => form.ending_status, (newStatus) => {
  if (!['sold', 'gifted'].includes(newStatus)) {
    form.buyer_name = '';
    form.buyer_phone = '';
    form.buyer_email = '';
    form.price = '';
  }

  if (newStatus !== 'loaned') {
    form.receiving_farm_name = '';
    form.receiver_name = '';
    form.receiver_phone = '';
    form.receiver_email = '';
    form.loan_date = '';
    form.return_date = '';
  }
});

const submit = () => {
  form.post(route('livestock-endings.store'));
};
</script>

<template>

  <Head title="Catat Pengakhiran Ternak" />

  <AppLayout title="Catat Pengakhiran Ternak">
    <div class="py-2">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-2xl font-bold">Catat Pengakhiran Ternak</h1>
          <p class="text-muted-foreground">Catat ternak yang sudah tidak aktif di peternakan</p>
        </div>
        <form @submit.prevent="submit">
          <Card>
            <CardHeader>
              <CardTitle>Informasi Pengakhiran</CardTitle>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Selected Livestock Info -->
              <div v-if="selectedLivestock"
                class="p-6 bg-gradient-to-r from-primary/10 to-primary/5 rounded-lg border-l-4 border-primary">
                <h3 class="font-semibold mb-4 text-lg text-primary">Informasi Ternak yang Akan Diakhiri</h3>

                <!-- Main livestock info grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-4">
                  <!-- Basic Info -->
                  <div class="space-y-3">
                    <h4 class="font-medium text-sm text-muted-foreground uppercase tracking-wide">Informasi Dasar</h4>
                    <div class="space-y-2">
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Nama:</span>
                        <span class="text-sm">{{ selectedLivestock.name }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">ID AiFarm:</span>
                        <span class="text-sm font-mono bg-background px-2 py-1 rounded">{{ selectedLivestock.aifarm_id
                        }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Tag ID:</span>
                        <span class="text-sm font-mono bg-background px-2 py-1 rounded">{{ selectedLivestock.tag_id
                        }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Breed & Species Info -->
                  <div class="space-y-3">
                    <h4 class="font-medium text-sm text-muted-foreground uppercase tracking-wide">Jenis & Ras</h4>
                    <div class="space-y-2">
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Spesies:</span>
                        <span class="text-sm">{{ selectedLivestock.breed?.species?.name || '-' }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Ras:</span>
                        <span class="text-sm">{{ selectedLivestock.breed?.name || '-' }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Jenis Kelamin:</span>
                        <span class="text-sm">{{ selectedLivestock.sex === 'M' ? 'Jantan' : 'Betina' }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Physical Info -->
                  <div class="space-y-3">
                    <h4 class="font-medium text-sm text-muted-foreground uppercase tracking-wide">Informasi Fisik</h4>
                    <div class="space-y-2">
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Bobot Terakhir:</span>
                        <span class="text-sm">
                          {{ selectedLivestock.weight ? selectedLivestock.weight + ' kg' : 'Belum diukur' }}
                        </span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Tanggal Lahir:</span>
                        <span class="text-sm">
                          {{ selectedLivestock.birthdate ? new
                            Date(selectedLivestock.birthdate).toLocaleDateString('id-ID') : 'Tidak diketahui' }}
                        </span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Umur:</span>
                        <span class="text-sm">
                          {{ selectedLivestock.age_in_year ? selectedLivestock.age_in_year + ' tahun ' +
                            (selectedLivestock.age_in_month % 12) + ' bulan' : 'Tidak diketahui' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Additional Info -->
                <div class="pt-4 border-t border-border">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Farm & Group Info -->
                    <div class="space-y-2">
                      <h4 class="font-medium text-sm text-muted-foreground">Kandang & Kelompok</h4>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Kelompok:</span>
                        <span class="text-sm">
                          {{ selectedLivestock.herd?.name || 'Tidak ada kelompok' }}
                        </span>
                      </div>
                    </div>

                    <!-- Status Info -->
                    <div class="space-y-2">
                      <h4 class="font-medium text-sm text-muted-foreground">Status Saat Ini</h4>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">Status:</span>
                        <span
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                          Aktif
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="form.errors.livestock_id"
                  class="text-sm text-red-500 mt-4 p-2 bg-red-50 dark:bg-red-900/20 rounded">
                  {{ form.errors.livestock_id }}
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-4">

                  <!-- Ending Date -->
                  <div class="space-y-2">
                    <Label for="ending_date">Tanggal Pengakhiran *</Label>
                    <Input id="ending_date" v-model="form.ending_date" type="date" :disabled="form.processing"
                      :class="{ 'border-red-500': form.errors.ending_date }" />
                    <div v-if="form.errors.ending_date" class="text-sm text-red-500">
                      {{ form.errors.ending_date }}
                    </div>
                  </div>

                  <!-- Ending Status -->
                  <div class="space-y-2">
                    <Label for="ending_status">Status Pengakhiran *</Label>
                    <Select v-model="form.ending_status" :disabled="form.processing">
                      <SelectTrigger :class="{ 'border-red-500': form.errors.ending_status }">
                        <SelectValue placeholder="Pilih status pengakhiran" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="(label, value) in statusOptions" :key="value" :value="value">
                          {{ label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <div v-if="form.errors.ending_status" class="text-sm text-red-500">
                      {{ form.errors.ending_status }}
                    </div>
                  </div>
                </div>
                <div>

                  <!-- Buyer Fields (for sold/gifted) -->
                  <div v-if="showBuyerFields" class="space-y-4 p-4 border rounded-lg">
                    <h3 class="font-medium">Informasi Pembeli/Penerima</h3>

                    <div class="grid grid-cols-1 gap-4">
                      <div class="space-y-2">
                        <Label for="buyer_name">Nama *</Label>
                        <Input id="buyer_name" v-model="form.buyer_name" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.buyer_name }" />
                        <div v-if="form.errors.buyer_name" class="text-sm text-red-500">
                          {{ form.errors.buyer_name }}
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label for="buyer_phone">Nomor Telepon *</Label>
                        <Input id="buyer_phone" v-model="form.buyer_phone" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.buyer_phone }" />
                        <div v-if="form.errors.buyer_phone" class="text-sm text-red-500">
                          {{ form.errors.buyer_phone }}
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label for="buyer_email">Email</Label>
                        <Input id="buyer_email" v-model="form.buyer_email" type="email" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.buyer_email }" />
                        <div v-if="form.errors.buyer_email" class="text-sm text-red-500">
                          {{ form.errors.buyer_email }}
                        </div>
                      </div>

                      <div v-if="showPriceField" class="space-y-2">
                        <Label for="price">Harga *</Label>
                        <Input id="price" v-model="form.price" type="number" min="0" step="0.01"
                          :disabled="form.processing" :class="{ 'border-red-500': form.errors.price }" />
                        <div v-if="form.errors.price" class="text-sm text-red-500">
                          {{ form.errors.price }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Loan Fields -->
                  <div v-if="showLoanFields" class="space-y-4 p-4 border rounded-lg">
                    <h3 class="font-medium">Informasi Peminjaman</h3>

                    <div class="grid grid-cols-1 gap-4">
                      <div class="space-y-2">
                        <Label for="receiving_farm_name">Nama Peternakan Penerima *</Label>
                        <Input id="receiving_farm_name" v-model="form.receiving_farm_name" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.receiving_farm_name }" />
                        <div v-if="form.errors.receiving_farm_name" class="text-sm text-red-500">
                          {{ form.errors.receiving_farm_name }}
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label for="receiver_name">Nama Penerima *</Label>
                        <Input id="receiver_name" v-model="form.receiver_name" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.receiver_name }" />
                        <div v-if="form.errors.receiver_name" class="text-sm text-red-500">
                          {{ form.errors.receiver_name }}
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label for="receiver_phone">Nomor Telepon *</Label>
                        <Input id="receiver_phone" v-model="form.receiver_phone" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.receiver_phone }" />
                        <div v-if="form.errors.receiver_phone" class="text-sm text-red-500">
                          {{ form.errors.receiver_phone }}
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label for="receiver_email">Email</Label>
                        <Input id="receiver_email" v-model="form.receiver_email" type="email"
                          :disabled="form.processing" :class="{ 'border-red-500': form.errors.receiver_email }" />
                        <div v-if="form.errors.receiver_email" class="text-sm text-red-500">
                          {{ form.errors.receiver_email }}
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label for="loan_date">Tanggal Dipinjam *</Label>
                        <Input id="loan_date" v-model="form.loan_date" type="date" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.loan_date }" />
                        <div v-if="form.errors.loan_date" class="text-sm text-red-500">
                          {{ form.errors.loan_date }}
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label for="return_date">Tanggal Kembali</Label>
                        <Input id="return_date" v-model="form.return_date" type="date" :disabled="form.processing"
                          :class="{ 'border-red-500': form.errors.return_date }" />
                        <div v-if="form.errors.return_date" class="text-sm text-red-500">
                          {{ form.errors.return_date }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div class="space-y-2 hidden">
                <Label for="notes">Catatan</Label>
                <Textarea id="notes" v-model="form.notes" placeholder="Catatan tambahan..."
                  :disabled="form.processing" />
              </div>

              <!-- Submit Button -->
              <div class="flex justify-between items-center">
                <Button type="button" variant="outline" @click="$inertia.visit(route('livestock-endings.index'))"
                  :disabled="form.processing">
                  Riwayat
                </Button>
                <div class="flex space-x-2">
                  <Button type="button" variant="outline" @click="$inertia.visit(selectedLivestock ? route('livestocks.show', selectedLivestock.id) : route('livestocks.index'))"
                    :disabled="form.processing">
                    Batal
                  </Button>
                  <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </form>
      </div>
    </div>
  </AppLayout>
</template>