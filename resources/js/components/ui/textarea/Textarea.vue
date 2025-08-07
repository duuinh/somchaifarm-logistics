<script setup lang="ts">
import { cn } from '@/lib/utils';

interface TextareaProps {
    class?: string;
    modelValue?: string;
    rows?: number;
    cols?: number;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
}

const props = defineProps<TextareaProps>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    change: [value: Event];
    input: [value: Event];
    focus: [value: FocusEvent];
    blur: [value: FocusEvent];
}>();
</script>

<template>
    <textarea
        :value="props.modelValue"
        :class="cn('flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50', props.class)"
        :rows="props.rows"
        :cols="props.cols"
        :placeholder="props.placeholder"
        :disabled="props.disabled"
        :readonly="props.readonly"
        :required="props.required"
        @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        @change="emit('change', $event)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
    />
</template>