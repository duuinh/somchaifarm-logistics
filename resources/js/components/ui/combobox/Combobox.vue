<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { ChevronDown, X } from 'lucide-vue-next';

interface ComboboxOption {
  value: string;
  label: string;
}

interface ComboboxProps {
  options: ComboboxOption[];
  modelValue?: string | number;
  placeholder?: string;
  disabled?: boolean;
}

const props = defineProps<ComboboxProps>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const searchValue = ref('');
const isOpen = ref(false);
const inputRef = ref<HTMLInputElement>();
const dropdownRef = ref<HTMLDivElement>();

const selectedOption = computed(() => {
  return props.options.find(option => option.value === props.modelValue);
});

const filteredOptions = computed(() => {
  if (!searchValue.value) return props.options;
  
  return props.options.filter(option =>
    option.label.toLowerCase().includes(searchValue.value.toLowerCase())
  );
});

const displayValue = computed(() => {
  if (isOpen.value) return searchValue.value;
  return selectedOption.value?.label || '';
});

const handleInputFocus = () => {
  isOpen.value = true;
  searchValue.value = '';
};

const handleInputBlur = () => {
  // Delay closing to allow option selection
  setTimeout(() => {
    isOpen.value = false;
    searchValue.value = '';
  }, 150);
};

const handleOptionSelect = (option: ComboboxOption) => {
  emit('update:modelValue', option.value);
  isOpen.value = false;
  searchValue.value = '';
  inputRef.value?.blur();
};

const clearSelection = () => {
  emit('update:modelValue', '');
  searchValue.value = '';
  inputRef.value?.focus();
};

// Close dropdown when clicking outside
onMounted(() => {
  const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
      isOpen.value = false;
      searchValue.value = '';
    }
  };

  document.addEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <div class="relative">
      <input
        ref="inputRef"
        :value="displayValue"
        @input="searchValue = ($event.target as HTMLInputElement).value"
        @focus="handleInputFocus"
        @blur="handleInputBlur"
        :placeholder="props.placeholder || 'Select option...'"
        :disabled="props.disabled"
        class="flex h-9 w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 pr-10 text-sm shadow-sm placeholder:text-gray-500 hover:border-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
        autocomplete="off"
      />
      
      <div class="absolute inset-y-0 right-0 flex items-center pr-2">
        <button
          v-if="selectedOption && !props.disabled"
          @click="clearSelection"
          type="button"
          class="p-1 hover:bg-gray-100 rounded"
        >
          <X class="h-3 w-3 text-gray-400" />
        </button>
        <ChevronDown class="h-4 w-4 text-gray-400 ml-1" />
      </div>
    </div>

    <div
      v-if="isOpen"
      class="absolute z-50 mt-1 w-full rounded-md border bg-white shadow-lg"
    >
      <div class="max-h-60 overflow-y-auto p-1">
        <div
          v-if="filteredOptions.length === 0"
          class="py-2 px-3 text-sm text-gray-500 text-center"
        >
          ไม่พบรายการ
        </div>
        <div
          v-for="option in filteredOptions"
          :key="option.value"
          @mousedown="handleOptionSelect(option)"
          class="relative flex cursor-pointer select-none items-center rounded-sm px-3 py-2 text-sm hover:bg-gray-100"
          :class="{
            'bg-blue-50 text-blue-900': String(props.modelValue || '') === option.value
          }"
        >
          {{ option.label }}
        </div>
      </div>
    </div>
  </div>
</template>