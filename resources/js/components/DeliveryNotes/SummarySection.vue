<template>
    <div class="mt-4 bg-gray-50 p-4 rounded-lg">
        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="font-medium">น้ำหนักรวม:</span>
                <span>{{ Number(totalWeight).toFixed(2) }} กก.</span>
            </div>
            <div class="flex justify-between">
                <span>รวมค่าสินค้า:</span>
                <span>{{ Number(itemsTotal).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท</span>
            </div>
            <div class="flex justify-between">
                <span>ค่าผสม:</span>
                <span>{{ Number(serviceFee).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท</span>
            </div>
            <div class="flex justify-between">
                <span>ค่ากระสอบ:</span>
                <span>{{ Number(bagFee).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท</span>
            </div>
            <div class="flex justify-between">
                <span>ค่าขนส่ง:</span>
                <span>{{ Number(transportFee).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท</span>
            </div>
            <div class="border-t pt-2">
                <div class="flex justify-between">
                    <span class="font-medium text-lg">รวมทั้งหมด:</span>
                    <span class="font-medium text-lg">{{ Number(totalAmount).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท</span>
                </div>
                <BahtTextDisplay :baht-text="bahtText" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import BahtTextDisplay from '@/components/BahtTextDisplay.vue';

interface Props {
    totalWeight: number | string;
    itemsTotal: number | string;
    serviceFee: number | string;
    bagFee: number | string;
    transportFee: number | string;
    totalAmount: number | string;
    bahtText: string;
}

defineProps<Props>();
</script>