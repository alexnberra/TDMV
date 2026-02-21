<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/ui';

interface Props {
    id: string;
    label?: string;
    modelValue?: string | number;
    type?: string;
    placeholder?: string;
    required?: boolean;
    error?: string;
    helper?: string;
    autocomplete?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: '',
    modelValue: '',
    type: 'text',
    placeholder: '',
    required: false,
    error: '',
    helper: '',
    autocomplete: '',
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const inputClass = computed(() => {
    return cn(
        'focus-ring h-11 w-full rounded-lg border bg-white px-3 py-2 text-base text-gray-900 placeholder:text-gray-400',
        props.error ? 'border-red-400' : 'border-gray-300',
        props.disabled ? 'bg-gray-100 text-gray-500' : 'hover:border-gray-400',
    );
});
</script>

<template>
    <label :for="id" class="block">
        <span v-if="label" class="mb-1.5 block text-sm font-semibold text-gray-700">
            {{ label }}
            <span v-if="required" class="ml-1 text-red-500">*</span>
        </span>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :autocomplete="autocomplete || undefined"
            :class="inputClass"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
        <span v-if="error" class="mt-1.5 block text-xs text-red-700">{{ error }}</span>
        <span v-else-if="helper" class="mt-1.5 block text-xs text-gray-500">{{ helper }}</span>
    </label>
</template>
