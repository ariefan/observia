<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { toast } from 'vue-sonner';
import {
  Building2,
  Users,
  Beef,
  Milk,
  TrendingUp,
  MapPin,
  BarChart3,
  PieChart,
  Activity,
  UserPlus,
  Trash2,
  Users2,
  Mars,
  Venus
} from 'lucide-vue-next';

interface Props {
  stats: {
    total_farms: number;
    active_farms: number;
    farms_with_livestock: number;
    total_users: number;
    super_users: number;
    users_with_farms: number;
    total_livestock: number;
    male_livestock: number;
    female_livestock: number;
    livestock_with_photos: number;
    total_herds: number;
    herds_with_livestock: number;
    total_rations: number;
    total_feeds: number;
    farms_created_last_30_days: number;
    users_registered_last_30_days: number;
    livestock_added_last_30_days: number;
  };
  topFarmsByLivestock: Array<{
    id: string;
    name: string;
    livestock_count: number;
    location: string;
  }>;
  topFarmsByUsers: Array<{
    id: string;
    name: string;
    users_count: number;
    location: string;
  }>;
  farmsByRegion: Array<{
    province: string;
    count: number;
  }>;
  livestockBySpecies: Array<{
    name: string;
    count: number;
  }>;
  superusers: Array<{
    id: string;
    name: string;
    email: string;
    created_at: string;
  }>;
}

const props = defineProps<Props>();

// Get current user
const page = usePage<SharedData>();
const currentUser = page.props.auth.user;

// Dialog state
const open = ref(false);

// Form for creating superuser
const form = useForm({
  email: ''
});

// Submit form
const createSuperUser = () => {
  form.post(route('super.create-user'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      toast.success('Pengguna super berhasil dibuat!');
    },
    onError: (errors) => {
      if (errors.email) {
        toast.error(errors.email);
      }
    },
  });
};

// Remove superuser
const removeSuperUser = (userId: string, userName: string) => {
  if (confirm(`Apakah Anda yakin ingin menghapus hak akses pengguna super dari ${userName}?`)) {
    useForm({}).delete(route('super.remove-user', userId), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success(`Hak akses pengguna super berhasil dihapus dari ${userName}`);
      },
      onError: (errors) => {
        if (errors.error) {
          toast.error(errors.error);
        }
      },
    });
  }
};
</script>

<template>

  <Head title="Dashboard Super" />

  <AppLayout>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-primary">Dashboard Super</h1>
          <p class="text-muted-foreground">Statistik dan wawasan seluruh peternakan</p>
        </div>
        <div class="flex items-center gap-3">
          <Dialog v-model:open="open">
            <DialogTrigger as-child>
              <Button variant="outline" class="gap-2">
                <Users2 class="w-4 h-4" />
                Superuser
              </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-[600px]">
              <DialogHeader>
                <DialogTitle>Kelola Superuser</DialogTitle>
                <DialogDescription>
                  Kelola hak akses superuser. Tambahkan superuser baru atau hapus yang sudah ada.
                </DialogDescription>
              </DialogHeader>

              <!-- Add Superuser Form -->
              <form @submit.prevent="createSuperUser" class="border-b pb-4 mb-4">
                <div class="grid gap-4">
                  <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="email" class="text-right">
                      Email
                    </Label>
                    <Input id="email" v-model="form.email" type="email" placeholder="pengguna@contoh.com"
                      class="col-span-2" :class="{ 'border-red-500': form.errors.email }" />
                    <Button type="submit" :disabled="form.processing" size="sm">
                      <UserPlus class="w-4 h-4 mr-2" />
                      {{ form.processing ? 'Menambahkan...' : 'Tambah' }}
                    </Button>
                  </div>
                  <div v-if="form.errors.email" class="text-sm text-red-500 text-right">
                    {{ form.errors.email }}
                  </div>
                </div>
              </form>

              <!-- Superusers List -->
              <div class="space-y-2">
                <h4 class="text-sm font-medium">Superuser Saat Ini</h4>
                <div class="max-h-60 overflow-y-auto space-y-2">
                  <div v-for="superuser in props.superusers" :key="superuser.id"
                    class="flex items-center justify-between p-3 border rounded-lg">
                    <div class="flex-1">
                      <div class="font-medium">{{ superuser.name }}</div>
                      <div class="text-sm text-muted-foreground">{{ superuser.email }}</div>
                      <div v-if="superuser.id === currentUser.id" class="text-xs text-blue-600 mt-1">
                        (Anda)
                      </div>
                    </div>
                    <Button v-if="superuser.id !== currentUser.id"
                      @click="removeSuperUser(superuser.id, superuser.name)" variant="outline" size="sm"
                      class="text-red-600 hover:text-red-700 hover:bg-red-50">
                      <Trash2 class="w-4 h-4" />
                    </Button>
                    <Badge v-else variant="secondary" class="text-xs">
                      Superuser Saat Ini
                    </Badge>
                  </div>
                </div>
              </div>
            </DialogContent>
          </Dialog>
          <Badge variant="secondary" class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
            <Activity class="w-4 h-4 mr-1" />
            Akses Superuser
          </Badge>
        </div>
      </div>

      <!-- Main Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Farm Statistics -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Peternakan</CardTitle>
            <Building2 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_farms }}</div>
            <p class="text-xs text-muted-foreground">
              {{ stats.active_farms }} peternakan aktif
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Pengguna</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_users }}</div>
            <p class="text-xs text-muted-foreground">
              {{ stats.super_users }} superuser
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Ternak</CardTitle>
            <Beef class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_livestock }}</div>
            <p class="text-xs text-muted-foreground">
              <span class="inline-flex items-center gap-1">
                <Mars class="w-3 h-3 text-blue-500" /> {{ stats.male_livestock }}
                <span class="mx-1">|</span>
                <Venus class="w-3 h-3 text-pink-500" /> {{ stats.female_livestock }}
              </span>
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Kandang</CardTitle>
            <Milk class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_herds }}</div>
            <p class="text-xs text-muted-foreground">
              {{ stats.herds_with_livestock }} dengan ternak
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Activity -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Peternakan Baru (30h)</CardTitle>
            <TrendingUp class="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">{{ stats.farms_created_last_30_days }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pengguna Baru (30h)</CardTitle>
            <TrendingUp class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">{{ stats.users_registered_last_30_days }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Ternak Baru (30h)</CardTitle>
            <TrendingUp class="h-4 w-4 text-purple-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-purple-600">{{ stats.livestock_added_last_30_days }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Top Lists and Analytics -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Farms by Livestock -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <BarChart3 class="h-5 w-5" />
              Top Peternakan berdasarkan Ternak
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-1">
              <div v-for="(farm, index) in topFarmsByLivestock" :key="farm.id"
                class="flex items-center justify-between p-2 rounded-lg border">
                <div class="flex items-center gap-3">
                  <Badge variant="outline" class="w-8 h-8 rounded-full flex items-center justify-center">
                    {{ index + 1 }}
                  </Badge>
                  <div>
                    <p class="font-medium">{{ farm.name }}</p>
                    <p class="text-sm text-muted-foreground flex items-center gap-1">
                      <MapPin class="h-3 w-3" />
                      {{ farm.location }}
                    </p>
                  </div>
                </div>
                <Badge variant="secondary">{{ farm.livestock_count }} ternak</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Top Farms by Users -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Users class="h-5 w-5" />
              Top Peternakan berdasarkan Tim
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-1">
              <div v-for="(farm, index) in topFarmsByUsers" :key="farm.id"
                class="flex items-center justify-between p-2 rounded-lg border">
                <div class="flex items-center gap-3">
                  <Badge variant="outline" class="w-8 h-8 rounded-full flex items-center justify-center">
                    {{ index + 1 }}
                  </Badge>
                  <div>
                    <p class="font-medium">{{ farm.name }}</p>
                    <p class="text-sm text-muted-foreground flex items-center gap-1">
                      <MapPin class="h-3 w-3" />
                      {{ farm.location }}
                    </p>
                  </div>
                </div>
                <Badge variant="secondary">{{ farm.users_count }} anggota</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Farms by Region -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <MapPin class="h-5 w-5" />
              Peternakan berdasarkan Wilayah
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div v-for="region in farmsByRegion" :key="region.province" class="flex items-center justify-between">
                <span class="text-sm">{{ region.province || 'Tidak Diketahui' }}</span>
                <Badge variant="outline">{{ region.count }}</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Livestock by Species -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <PieChart class="h-5 w-5" />
              Ternak berdasarkan Spesies
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div v-for="species in livestockBySpecies" :key="species.name" class="flex items-center justify-between">
                <span class="text-sm">{{ species.name }}</span>
                <Badge variant="outline">{{ species.count }}</Badge>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Additional Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Peternakan dengan Ternak</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.farms_with_livestock }}</div>
            <p class="text-xs text-muted-foreground">
              {{ Math.round((stats.farms_with_livestock / stats.total_farms) * 100) }}% dari semua peternakan
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pengguna dengan Peternakan</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.users_with_farms }}</div>
            <p class="text-xs text-muted-foreground">
              {{ Math.round((stats.users_with_farms / stats.total_users) * 100) }}% dari semua pengguna
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Ternak dengan Foto</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.livestock_with_photos }}</div>
            <p class="text-xs text-muted-foreground">
              {{ Math.round((stats.livestock_with_photos / stats.total_livestock) * 100) }}% memiliki foto
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Sumber Pakan</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_feeds }}</div>
            <p class="text-xs text-muted-foreground">
              {{ stats.total_rations }} ransum tersedia
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>