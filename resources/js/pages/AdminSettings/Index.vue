<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Pengaturan Sistem
      </h2>
    </template>

    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <SecondSidebar current-route="admin.settings.index" />

      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
            Pengaturan Sistem
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola konfigurasi sistem dan pengaturan aplikasi
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <Button as-child>
            <Link :href="route('admin.settings.create')">
              <Plus class="h-4 w-4 mr-2" />
              Tambah Setting
            </Link>
          </Button>
        </div>
      </div>

      <!-- Settings Form -->
      <form @submit.prevent="saveSettings" class="space-y-8">
        <div v-for="(categorySettings, category) in settings" :key="category" 
             class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="pb-4 border-b border-gray-200 dark:border-gray-700 mb-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white capitalize">
                {{ getCategoryTitle(category) }}
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ getCategoryDescription(category) }}
              </p>
            </div>

            <div class="space-y-6">
              <div v-for="setting in categorySettings" :key="setting.id" class="grid grid-cols-3 gap-6">
                <div class="col-span-1">
                  <label :for="`setting-${setting.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ setting.label }}
                    <span v-if="setting.options?.required" class="text-red-500">*</span>
                  </label>
                  <p v-if="setting.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ setting.description }}
                  </p>
                </div>

                <div class="col-span-2">
                  <!-- Boolean Toggle -->
                  <div v-if="setting.type === 'boolean'" class="flex items-center">
                    <button
                      type="button"
                      @click="toggleBoolean(setting.id)"
                      :class="[
                        form.settings[setting.id].value === 'true' ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600',
                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                      ]"
                    >
                      <span
                        :class="[
                          form.settings[setting.id].value === 'true' ? 'translate-x-5' : 'translate-x-0',
                          'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                        ]"
                      ></span>
                    </button>
                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                      {{ form.settings[setting.id].value === 'true' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </div>

                  <!-- Other Input Types -->
                  <SettingValueInput
                    v-else
                    :type="setting.type"
                    v-model="form.settings[setting.id].value"
                    :setting="setting"
                    :readonly="setting.options?.readonly"
                  />

                  <FormFieldError :error="form.errors[`settings.${setting.id}.value`]" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-between items-center">
          <!-- Telegram Test Buttons -->
          <div v-if="hasTelegramSettings" class="flex space-x-2">
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="testTelegramConnection"
              :disabled="testingConnection"
            >
              <span v-if="testingConnection">Testing...</span>
              <span v-else>Test Koneksi</span>
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="sendTestMessage"
              :disabled="sendingTest"
            >
              <span v-if="sendingTest">Mengirim...</span>
              <span v-else>Test Pesan</span>
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="sendHealthAlertTest"
              :disabled="sendingHealthTest"
            >
              <span v-if="sendingHealthTest">Mengirim...</span>
              <span v-else>Test Kesehatan</span>
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="sendLoginNotificationTest"
              :disabled="sendingLoginTest"
            >
              <span v-if="sendingLoginTest">Mengirim...</span>
              <span v-else>Test Login</span>
            </Button>
          </div>

          <div class="flex space-x-3">
            <Button
              type="button"
              variant="outline"
              @click="resetForm"
            >
              Reset
            </Button>
            <Button
              type="submit"
              :disabled="form.processing"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Simpan Pengaturan</span>
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
import { Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { ref, computed } from 'vue';
import axios from 'axios';
import SettingValueInput from '@/components/SettingValueInput.vue';
import FormFieldError from '@/components/ui/FormFieldError.vue';
import { getCategoryTitle, getCategoryDescription } from '@/composables/useSettingsConstants';
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
  settings: Record<string, Setting[]>;
}

const props = defineProps<Props>();

// Initialize form data
const formData: Record<number, { value: string | null }> = {};
Object.values(props.settings).flat().forEach(setting => {
  formData[setting.id] = { value: setting.value };
});

const form = useForm({
  settings: formData,
});


const toggleBoolean = (settingId: number) => {
  const currentValue = form.settings[settingId].value;
  form.settings[settingId].value = currentValue === 'true' ? 'false' : 'true';
};

const saveSettings = () => {
  const settingsArray = Object.entries(form.settings).map(([id, data]) => ({
    id: parseInt(id),
    value: data.value,
  }));

  form.post(route('admin.settings.bulk-update'), {
    data: { settings: settingsArray },
    preserveScroll: true,
  });
};

const resetForm = () => {
  Object.values(props.settings).flat().forEach(setting => {
    form.settings[setting.id].value = setting.value;
  });
};

// Telegram testing functionality
const testingConnection = ref(false);
const sendingTest = ref(false);
const sendingHealthTest = ref(false);
const sendingLoginTest = ref(false);

const hasTelegramSettings = computed(() => {
  return Object.keys(props.settings).includes('telegram');
});

const testTelegramConnection = async () => {
  testingConnection.value = true;
  try {
    const response = await axios.post(route('telegram.test-connection'));
    if (response.data.success) {
      // Show success message
      console.log('Connection test successful');
    } else {
      // Show error message
      console.error('Connection test failed:', response.data.message);
    }
  } catch (error) {
    console.error('Error testing connection:', error);
  } finally {
    testingConnection.value = false;
  }
};

const sendTestMessage = async () => {
  sendingTest.value = true;
  try {
    const response = await axios.post(route('telegram.test-message'));
    if (response.data.success) {
      // Show success message
      console.log('Test message sent successfully');
    } else {
      // Show error message
      console.error('Failed to send test message:', response.data.message);
    }
  } catch (error) {
    console.error('Error sending test message:', error);
  } finally {
    sendingTest.value = false;
  }
};

const sendHealthAlertTest = async () => {
  sendingHealthTest.value = true;
  try {
    const response = await axios.post(route('telegram.health-alert-test'));
    if (response.data.success) {
      // Show success message
      console.log('Health alert test sent successfully');
    } else {
      // Show error message
      console.error('Failed to send health alert test:', response.data.message);
    }
  } catch (error) {
    console.error('Error sending health alert test:', error);
  } finally {
    sendingHealthTest.value = false;
  }
};

const sendLoginNotificationTest = async () => {
  sendingLoginTest.value = true;
  try {
    const response = await axios.post(route('telegram.login-notification-test'));
    if (response.data.success) {
      // Show success message
      console.log('Login notification test sent successfully');
    } else {
      // Show error message
      console.error('Failed to send login notification test:', response.data.message);
    }
  } catch (error) {
    console.error('Error sending login notification test:', error);
  } finally {
    sendingLoginTest.value = false;
  }
};
</script>