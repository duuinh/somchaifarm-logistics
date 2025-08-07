<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ref, watch } from 'vue';

interface SelectProps {
    class?: string;
    modelValue?: string;
    placeholder?: string;
    disabled?: boolean;
}

const props = defineProps<SelectProps>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const selectedValue = ref(props.modelValue || '');

watch(() => props.modelValue, (newValue) => {
    selectedValue.value = newValue || '';
});

const handleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    selectedValue.value = target.value;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <select
        :value="selectedValue"
        :class="cn('flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50', props.class)"
        :disabled="props.disabled"
        @change="handleChange"
    >
        <option v-if="props.placeholder" value="" disabled>{{ props.placeholder }}</option>
        <slot />
    </select>
</template>