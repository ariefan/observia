<template>
  <!-- Direct component rendering based on type -->
  <Input 
    v-if="type === 'text' || type === 'number'"
    v-model="localValue"
    :id="`setting-${setting?.id || 'value'}`"
    :type="type"
    :readonly="readonly"
  />
  
  <Textarea 
    v-else-if="type === 'textarea'"
    v-model="localValue"
    :id="`setting-${setting?.id || 'value'}`"
    :rows="setting?.options?.rows || 5"
    :placeholder="setting?.options?.placeholder || ''"
  />
  
  <Select 
    v-else-if="type === 'select'"
    v-model="localValue"
    :id="`setting-${setting?.id || 'value'}`"
  >
    <SelectTrigger>
      <SelectValue />
    </SelectTrigger>
    <SelectContent>
      <SelectItem 
        v-for="option in setting?.options?.choices || []" 
        :key="option.value" 
        :value="option.value"
      >
        {{ option.label }}
      </SelectItem>
    </SelectContent>
  </Select>
  
  <!-- Default to Input for unknown types -->
  <Input 
    v-else
    v-model="localValue"
    :id="`setting-${setting?.id || 'value'}`"
  />
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ref, watch } from 'vue';

interface Props {
  type: string;
  modelValue: string;
  setting?: any;
  readonly?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);

watch(localValue, (newValue) => {
  emit('update:modelValue', newValue);
});

watch(() => props.modelValue, (newValue) => {
  localValue.value = newValue;
});
</script>