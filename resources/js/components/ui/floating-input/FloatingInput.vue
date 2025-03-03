<script setup lang="ts">
import { ref } from 'vue';
import { Input } from '@/components/ui/input';

// Define props
interface Props {
    label: string;
    id?: string;
    inputClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    id: undefined,
    inputClass: ''
});

// Generate unique ID if not provided
const inputId = ref(props.id || `floating-input-${Math.random().toString(36).substring(2, 9)}`);
</script>

<template>
    <div class="relative">
        <Input :id="inputId" v-bind="$attrs" placeholder="" class="peer pt-4" :class="[
            inputClass,
            'block px-2.5 pb-2.5 pt-4 w-full text-sm appearance-none',
            'focus:outline-none focus:ring-0 bg-card'
        ]" />
        <label :for="inputId"
            class="absolute text-sm text-muted-foreground duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-card px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
            {{ label }}
        </label>
    </div>
</template>
