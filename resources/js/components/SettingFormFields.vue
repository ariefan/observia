<template>
  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <!-- Key -->
    <div>
      <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Key <span class="text-red-500">*</span>
      </label>
      <Input
        id="key"
        v-model="modelValue.key"
        type="text"
        required
        class="mt-1"
        placeholder="telegram_bot_token"
      />
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Identifier unik untuk setting (huruf kecil, underscore)
      </p>
      <FormFieldError :error="errors.key" />
    </div>

    <!-- Label -->
    <div>
      <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Label <span class="text-red-500">*</span>
      </label>
      <Input
        id="label"
        v-model="modelValue.label"
        type="text"
        required
        class="mt-1"
        placeholder="Telegram Bot Token"
      />
      <FormFieldError :error="errors.label" />
    </div>

    <!-- Type -->
    <div>
      <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Type <span class="text-red-500">*</span>
      </label>
      <Select v-model="modelValue.type" required>
        <SelectTrigger class="mt-1">
          <SelectValue placeholder="Pilih Type" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem 
            v-for="type in SETTING_TYPES" 
            :key="type.value" 
            :value="type.value"
          >
            {{ type.label }}
          </SelectItem>
        </SelectContent>
      </Select>
      <FormFieldError :error="errors.type" />
    </div>

    <!-- Category -->
    <div>
      <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Category <span class="text-red-500">*</span>
      </label>
      <Select v-model="modelValue.category" required>
        <SelectTrigger class="mt-1">
          <SelectValue placeholder="Pilih Category" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem 
            v-for="category in SETTING_CATEGORIES" 
            :key="category.value" 
            :value="category.value"
          >
            {{ category.label }}
          </SelectItem>
        </SelectContent>
      </Select>
      <FormFieldError :error="errors.category" />
    </div>

    <!-- Sort Order -->
    <div>
      <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Sort Order <span class="text-red-500">*</span>
      </label>
      <Input
        id="sort_order"
        v-model="modelValue.sort_order"
        type="number"
        min="0"
        required
        class="mt-1"
      />
      <FormFieldError :error="errors.sort_order" />
    </div>

    <!-- Is Active -->
    <div class="flex items-center space-x-3">
      <Checkbox
        id="is_active"
        v-model:checked="modelValue.is_active"
      />
      <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        Setting Aktif
      </label>
    </div>
  </div>

  <!-- Value -->
  <div>
    <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ valueLabel }}
    </label>
    <Input
      v-if="modelValue.type === 'text'"
      id="value"
      v-model="modelValue.value"
      type="text"
      class="mt-1"
    />
    <Input
      v-else-if="modelValue.type === 'number'"
      id="value"
      v-model="modelValue.value"
      type="number"
      class="mt-1"
    />
    <Textarea
      v-else-if="modelValue.type === 'textarea'"
      id="value"
      v-model="modelValue.value"
      :rows="3"
      class="mt-1"
    />
    <Select
      v-else-if="modelValue.type === 'boolean'"
      v-model="modelValue.value"
    >
      <SelectTrigger class="mt-1">
        <SelectValue />
      </SelectTrigger>
      <SelectContent>
        <SelectItem 
          v-for="option in BOOLEAN_OPTIONS" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </SelectItem>
      </SelectContent>
    </Select>
    <FormFieldError :error="errors.value" />
  </div>

  <!-- Description -->
  <div>
    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      Description
    </label>
    <Textarea
      id="description"
      v-model="modelValue.description"
      :rows="3"
      class="mt-1"
      placeholder="Deskripsi fungsi dan kegunaan setting ini"
    />
    <FormFieldError :error="errors.description" />
  </div>

  <!-- Options (JSON) -->
  <div>
    <label for="options" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      Options (JSON)
    </label>
    <Textarea
      id="options"
      v-model="modelValue.options"
      :rows="3"
      class="mt-1 font-mono text-sm"
      placeholder='{"required": true, "readonly": false}'
    />
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      Konfigurasi tambahan dalam format JSON (opsional)
    </p>
    <FormFieldError :error="errors.options" />
  </div>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import FormFieldError from '@/components/ui/FormFieldError.vue';
import { SETTING_TYPES, SETTING_CATEGORIES, BOOLEAN_OPTIONS } from '@/composables/useSettingsConstants';
import { computed } from 'vue';

interface FormData {
  key: string;
  label: string;
  value: string;
  type: string;
  options: string;
  description: string;
  category: string;
  sort_order: number;
  is_active: boolean;
}

interface Props {
  modelValue: FormData;
  errors: Record<string, string>;
  isEdit?: boolean;
}

const props = defineProps<Props>();

const valueLabel = computed(() => {
  return props.isEdit ? 'Value' : 'Default Value';
});
</script>