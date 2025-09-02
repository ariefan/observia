<template>
  <component 
    :is="inputComponent" 
    v-bind="inputProps"
    v-model="localValue"
  />
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { computed, ref, watch } from 'vue';

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

const inputComponent = computed(() => {
  switch (props.type) {
    case 'text':
    case 'number':
      return Input;
    case 'textarea':
      return Textarea;
    case 'boolean':
    case 'select':
      return Select;
    default:
      return Input;
  }
});

const inputProps = computed(() => {
  const baseProps: any = {
    id: `setting-${props.setting?.id || 'value'}`,
  };

  switch (props.type) {
    case 'text':
      return {
        ...baseProps,
        type: 'text',
        readonly: props.readonly,
      };
    case 'number':
      return {
        ...baseProps,
        type: 'number',
      };
    case 'textarea':
      return {
        ...baseProps,
        rows: 3,
      };
    case 'boolean':
      return {
        ...baseProps,
        children: [
          h(SelectTrigger, {}, [
            h(SelectValue)
          ]),
          h(SelectContent, {}, [
            h(SelectItem, { value: 'true' }, 'True'),
            h(SelectItem, { value: 'false' }, 'False')
          ])
        ]
      };
    case 'select':
      return {
        ...baseProps,
        children: [
          h(SelectTrigger, {}, [
            h(SelectValue)
          ]),
          h(SelectContent, {}, 
            props.setting?.options?.choices?.map((option: any) => 
              h(SelectItem, { key: option.value, value: option.value }, option.label)
            ) || []
          )
        ]
      };
    default:
      return baseProps;
  }
});
</script>