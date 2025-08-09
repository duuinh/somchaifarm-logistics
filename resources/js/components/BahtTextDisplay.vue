<template>
    <div class="mt-3 text-center p-3 bg-blue-50 rounded-lg border border-blue-200">
        <div class="text-lg font-semibold text-blue-900">{{ bahtText }}</div>
        <div class="text-sm text-blue-700 font-medium mb-1">จำนวนเงิน (ตัวอักษร)</div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    bahtText: string;
}

defineProps<Props>();
</script>