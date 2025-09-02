<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ isEdit ? 'Edit Setting' : 'Tambah Setting Baru' }}
      </h2>
    </template>

    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <SecondSidebar current-route="admin.settings.index" />

      <div class="flex-1 flex flex-col gap-4 p-4 max-w-4xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center space-x-4">
          <Link 
            :href="route('admin.settings.index')" 
            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl">
              {{ isEdit ? 'Edit Setting' : 'Tambah Setting Baru' }}
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              {{ isEdit ? `Ubah konfigurasi setting: ${setting?.label}` : 'Buat konfigurasi setting baru untuk sistem' }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
          <SettingFormFields 
            v-model="form" 
            :errors="form.errors" 
            :is-edit="isEdit"
          />

          <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
            <!-- Delete Button (Edit only) -->
            <Button
              v-if="isEdit"
              type="button"
              variant="destructive"
              @click="handleDelete"
              :disabled="deleteForm.processing"
            >
              <span v-if="deleteForm.processing">Menghapus...</span>
              <span v-else>Hapus Setting</span>
            </Button>
            <div v-else></div>
            
            <div class="flex space-x-3">
              <Button as-child variant="outline">
                <Link :href="route('admin.settings.index')">
                  Batal
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>{{ isEdit ? 'Update Setting' : 'Simpan Setting' }}</span>
              </Button>
            </div>
          </div>
        </form>
      </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import SettingFormFields from '@/components/SettingFormFields.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';

interface Setting {
  id: number;
  key: string;
  label: string;
  value: string | null;
  type: string;
  options: any;
  description: string | null;
  category: string;
  sort_order: number;
  is_active: boolean;
}

interface Props {
  setting?: Setting;
}

const props = defineProps<Props>();

const isEdit = !!props.setting;

// Initialize form with setting data or defaults
const form = useForm({
  key: props.setting?.key || '',
  label: props.setting?.label || '',
  value: props.setting?.value || '',
  type: props.setting?.type || '',
  options: props.setting?.options ? JSON.stringify(props.setting.options, null, 2) : '',
  description: props.setting?.description || '',
  category: props.setting?.category || '',
  sort_order: props.setting?.sort_order || 0,
  is_active: props.setting?.is_active ?? true,
});

const deleteForm = useForm({});

const handleSubmit = () => {
  if (isEdit) {
    form.put(route('admin.settings.update', props.setting!.id));
  } else {
    form.post(route('admin.settings.store'));
  }
};

const handleDelete = () => {
  if (confirm('Apakah Anda yakin ingin menghapus setting ini?')) {
    deleteForm.delete(route('admin.settings.destroy', props.setting!.id));
  }
};
</script>