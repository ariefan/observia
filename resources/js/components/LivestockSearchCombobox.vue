<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import axios from "axios";
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox'
import { Button } from "@/components/ui/button";
import { Check, ChevronsUpDown } from "lucide-vue-next";

interface Livestock {
  id: string;
  name: string;
  aifarm_id: string;
  tag_id: string;
  sex: 'M' | 'F';
}

interface Props {
  modelValue?: Livestock | null | undefined;
  placeholder?: string;
  sexFilter?: 'M' | 'F';
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Pilih Ternak',
  disabled: false
});

const emit = defineEmits<{
  'update:modelValue': [value: Livestock | null];
}>();

const selectedLivestock = ref<Livestock | null>(props.modelValue || null);
const searchQuery = ref('');
const searchResults = ref<Livestock[]>([]);

const getDisplayValue = (livestock: Livestock | null) => {
  if (!livestock) return '';
  return `${livestock.aifarm_id} - ${livestock.name}`;
};

const loadLivestock = async (query: string = '') => {
  try {
    const params: Record<string, string> = { q: query };
    if (props.sexFilter) {
      params.sex = props.sexFilter;
    }
    
    const response = await axios.get(route('livestocks.search', params));
    searchResults.value = response.data;
  } catch (error) {
    console.error('Error loading livestock:', error);
    searchResults.value = [];
  }
};

watch(searchQuery, async (newQuery) => {
  if (!newQuery || newQuery.length === 0) {
    await loadLivestock();
    return;
  }

  if (newQuery.length < 2) {
    return;
  }

  await loadLivestock(newQuery);
});

watch(selectedLivestock, (newValue) => {
  emit('update:modelValue', newValue);
});

watch(() => props.modelValue, (newValue) => {
  selectedLivestock.value = newValue || null;
});

onMounted(() => {
  loadLivestock();
});
</script>

<template>
  <Combobox 
    v-model="selectedLivestock" 
    v-model:search-term="searchQuery"
    :display-value="getDisplayValue"
    :disabled="disabled"
  >
    <ComboboxAnchor as-child>
      <ComboboxTrigger as-child>
        <Button variant="outline" class="justify-between w-full" :disabled="disabled">
          {{ selectedLivestock ? getDisplayValue(selectedLivestock) : placeholder }}
          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </ComboboxTrigger>
    </ComboboxAnchor>

    <ComboboxList class="w-full">
      <ComboboxInput v-model="searchQuery" placeholder="Cari ternak..." />
      <ComboboxEmpty>Ternak tidak ditemukan.</ComboboxEmpty>
      <ComboboxGroup>
        <ComboboxItem v-for="result in searchResults" :key="result.id" :value="result">
          {{ result.tag_id }} - {{ result.name }}
          <ComboboxItemIndicator>
            <Check class="ml-auto h-4 w-4" />
          </ComboboxItemIndicator>
        </ComboboxItem>
      </ComboboxGroup>
    </ComboboxList>
  </Combobox>
</template>