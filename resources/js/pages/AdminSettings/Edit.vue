<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
              Edit Setting
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              Ubah konfigurasi setting: {{ setting.label }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Key -->
            <div>
              <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Key <span class="text-red-500">*</span>
              </label>
              <Input
                id="key"
                v-model="form.key"
                type="text"
                required
                class="mt-1"
                placeholder="telegram_bot_token"
              />
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Identifier unik untuk setting (huruf kecil, underscore)
              </p>
              <div v-if="form.errors.key" class="mt-1">
                <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.key }}</p>
              </div>
            </div>

            <!-- Label -->
            <div>
              <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Label <span class="text-red-500">*</span>
              </label>
              <Input
                id="label"
                v-model="form.label"
                type="text"
                required
                class="mt-1"
                placeholder="Telegram Bot Token"
              />
              <div v-if="form.errors.label" class="mt-1">
                <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.label }}</p>
              </div>
            </div>

            <!-- Type -->
            <div>
              <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Type <span class="text-red-500">*</span>
              </label>
              <Select v-model="form.type" required>
                <SelectTrigger class="mt-1">
                  <SelectValue placeholder="Pilih Type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="text">Text</SelectItem>
                  <SelectItem value="number">Number</SelectItem>
                  <SelectItem value="boolean">Boolean</SelectItem>
                  <SelectItem value="textarea">Textarea</SelectItem>
                  <SelectItem value="select">Select</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.type" class="mt-1">
                <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.type }}</p>
              </div>
            </div>

            <!-- Category -->
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Category <span class="text-red-500">*</span>
              </label>
              <Select v-model="form.category" required>
                <SelectTrigger class="mt-1">
                  <SelectValue placeholder="Pilih Category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="general">General</SelectItem>
                  <SelectItem value="telegram">Telegram</SelectItem>
                  <SelectItem value="notifications">Notifications</SelectItem>
                  <SelectItem value="system">System</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.category" class="mt-1">
                <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.category }}</p>
              </div>
            </div>

            <!-- Sort Order -->
            <div>
              <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Sort Order <span class="text-red-500">*</span>
              </label>
              <Input
                id="sort_order"
                v-model="form.sort_order"
                type="number"
                min="0"
                required
                class="mt-1"
              />
              <div v-if="form.errors.sort_order" class="mt-1">
                <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.sort_order }}</p>
              </div>
            </div>

            <!-- Is Active -->
            <div class="flex items-center space-x-3">
              <Checkbox
                id="is_active"
                v-model:checked="form.is_active"
              />
              <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Setting Aktif
              </label>
            </div>
          </div>

          <!-- Value -->
          <div>
            <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Value
            </label>
            <Input
              v-if="form.type === 'text'"
              id="value"
              v-model="form.value"
              type="text"
              class="mt-1"
            />
            <Input
              v-else-if="form.type === 'number'"
              id="value"
              v-model="form.value"
              type="number"
              class="mt-1"
            />
            <Textarea
              v-else-if="form.type === 'textarea'"
              id="value"
              v-model="form.value"
              :rows="3"
              class="mt-1"
            />
            <Select
              v-else-if="form.type === 'boolean'"
              v-model="form.value"
            >
              <SelectTrigger class="mt-1">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="true">True</SelectItem>
                <SelectItem value="false">False</SelectItem>
              </SelectContent>
            </Select>
            <div v-if="form.errors.value" class="mt-1">
              <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.value }}</p>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Description
            </label>
            <Textarea
              id="description"
              v-model="form.description"
              :rows="3"
              class="mt-1"
              placeholder="Deskripsi fungsi dan kegunaan setting ini"
            />
            <div v-if="form.errors.description" class="mt-1">
              <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</p>
            </div>
          </div>

          <!-- Options (JSON) -->
          <div>
            <label for="options" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Options (JSON)
            </label>
            <Textarea
              id="options"
              v-model="form.options"
              :rows="3"
              class="mt-1 font-mono text-sm"
              placeholder='{"required": true, "readonly": false}'
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Konfigurasi tambahan dalam format JSON (opsional)
            </p>
            <div v-if="form.errors.options" class="mt-1">
              <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.options }}</p>
            </div>
          </div>

          <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
            <Button
              type="button"
              variant="destructive"
              @click="deleteSetting"
              :disabled="deleteForm.processing"
            >
              <span v-if="deleteForm.processing">Menghapus...</span>
              <span v-else>Hapus Setting</span>
            </Button>
            
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
                <span v-else>Update Setting</span>
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';

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
  setting: Setting;
}

const props = defineProps<Props>();

const form = useForm({
  key: props.setting.key,
  label: props.setting.label,
  value: props.setting.value || '',
  type: props.setting.type,
  options: props.setting.options ? JSON.stringify(props.setting.options, null, 2) : '',
  description: props.setting.description || '',
  category: props.setting.category,
  sort_order: props.setting.sort_order,
  is_active: props.setting.is_active,
});

const deleteForm = useForm({});

const submit = () => {
  form.put(route('admin.settings.update', props.setting.id));
};

const deleteSetting = () => {
  if (confirm('Apakah Anda yakin ingin menghapus setting ini?')) {
    deleteForm.delete(route('admin.settings.destroy', props.setting.id));
  }
};
</script>