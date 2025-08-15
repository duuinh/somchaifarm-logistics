<template>
    <div class="mb-6 border-2 border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700">ค่าบริการเพิ่มเติม</h4>
        </div>
        <Table>
            <TableHeader>
                <TableRow class="bg-gray-50">
                    <TableHead class="font-semibold w-16">เลือก</TableHead>
                    <TableHead class="font-semibold">รายการ</TableHead>
                    <TableHead class="text-center font-semibold">จำนวน</TableHead>
                    <TableHead class="text-center font-semibold">หน่วย</TableHead>
                    <TableHead class="text-right font-semibold">ราคาต่อหน่วย (บาท)</TableHead>
                    <TableHead class="text-right font-semibold">ราคารวม</TableHead>
                    <TableHead class="w-20"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <!-- Service Fee Row -->
                <TableRow class="hover:bg-orange-50">
                    <TableCell>
                        <Checkbox
                            :model-value="includeServiceFee"
                            @update:model-value="$emit('update:includeServiceFee', $event)"
                        />
                    </TableCell>
                    <TableCell class="font-medium">ค่าผสมอาหาร</TableCell>
                    <TableCell class="text-center">
                        <div class="flex justify-center">
                            <div class="space-y-1">
                                <Input
                                    v-model="serviceTonsInput"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="0"
                                    :class="{
                                        'w-24 text-center': true,
                                        'border-red-200 bg-red-50': serviceTonsError
                                    }"
                                    :disabled="!includeServiceFee"
                                />
                                <div v-if="serviceTonsError && includeServiceFee" class="text-xs text-red-500 text-center">
                                    {{ serviceTonsError }}
                                </div>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-orange-50 text-orange-700 text-sm font-medium">
                            ตัน
                        </span>
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="space-y-1">
                                <Input
                                    v-model="serviceFeePerTonInput"
                                    type="text"
                                    inputmode="decimal"
                                    placeholder="300"
                                    :class="{
                                        'w-24 text-right': true,
                                        'border-red-200 bg-red-50': serviceFeePerTonError
                                    }"
                                    :disabled="!includeServiceFee"
                                />
                                <div v-if="serviceFeePerTonError && includeServiceFee" class="text-xs text-red-500 text-right">
                                    {{ serviceFeePerTonError }}
                                </div>
                            </div>
                            <span class="text-sm text-gray-500">บาท/ตัน</span>
                        </div>
                    </TableCell>
                    <TableCell class="text-right font-semibold text-blue-600">
                        {{ includeServiceFee ? Number(calculatedServiceFee).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00' }} บาท
                    </TableCell>
                    <TableCell></TableCell>
                </TableRow>

                <!-- Bag Fee Row -->
                <TableRow class="hover:bg-blue-50">
                    <TableCell>
                        <Checkbox
                            :model-value="includeBagFee"
                            @update:model-value="$emit('update:includeBagFee', $event)"
                        />
                    </TableCell>
                    <TableCell class="font-medium">ค่ากระสอบ</TableCell>
                    <TableCell class="text-center">
                        <div class="flex justify-center">
                            <div class="space-y-1">
                                <Input
                                    v-model="bagFeeInput"
                                    type="text"
                                    inputmode="decimal"
                                    placeholder="0"
                                    :class="{
                                        'w-24 text-center': true,
                                        'border-red-200 bg-red-50': bagFeeError
                                    }"
                                    :disabled="!includeBagFee"
                                />
                                <div v-if="bagFeeError && includeBagFee" class="text-xs text-red-500 text-center">
                                    {{ bagFeeError }}
                                </div>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-medium">
                            บาท
                        </span>
                    </TableCell>
                    <TableCell class="text-right">-</TableCell>
                    <TableCell class="text-right font-semibold text-blue-600">
                        {{ includeBagFee ? Number(bagFee || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00' }} บาท
                    </TableCell>
                    <TableCell></TableCell>
                </TableRow>

                <!-- Transport Fee Row -->
                <TableRow class="hover:bg-purple-50">
                    <TableCell>
                        <Checkbox
                            :model-value="includeTransportFee"
                            @update:model-value="$emit('update:includeTransportFee', $event)"
                        />
                    </TableCell>
                    <TableCell class="font-medium">ค่าขนส่ง</TableCell>
                    <TableCell class="text-center">
                        <div class="flex justify-center">
                            <div class="space-y-1">
                                <Input
                                    v-model="transportFeeInput"
                                    type="text"
                                    inputmode="decimal"
                                    placeholder="0"
                                    :class="{
                                        'w-24 text-center': true,
                                        'border-red-200 bg-red-50': transportFeeError
                                    }"
                                    :disabled="!includeTransportFee"
                                />
                                <div v-if="transportFeeError && includeTransportFee" class="text-xs text-red-500 text-center">
                                    {{ transportFeeError }}
                                </div>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-medium">
                            บาท
                        </span>
                    </TableCell>
                    <TableCell class="text-right">-</TableCell>
                    <TableCell class="text-right font-semibold text-blue-600">
                        {{ includeTransportFee ? Number(transportFee || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00' }} บาท
                    </TableCell>
                    <TableCell></TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>

<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { ref, watch } from 'vue';
import { useField } from 'vee-validate';

interface Props {
    includeServiceFee: boolean;
    includeBagFee: boolean;
    includeTransportFee: boolean;
    serviceFeetons: number | string;
    serviceFeePerTon: number | string;
    bagFee: number | string;
    transportFee: number | string;
    calculatedServiceFee: number | string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:includeServiceFee': [value: boolean];
    'update:includeBagFee': [value: boolean];
    'update:includeTransportFee': [value: boolean];
    'update:serviceFeetons': [value: number];
    'update:serviceFeePerTon': [value: number];
    'update:bagFee': [value: number];
    'update:transportFee': [value: number];
}>();

// VeeValidate fields for service fees
const serviceTonsValidationRule = (value: string) => {
    if (!value) return true; // Allow empty
    const pattern = /^[0-9]+$/;
    if (!pattern.test(value)) {
        return 'จำนวนตันต้องเป็นจำนวนเต็ม';
    }
    const num = parseInt(value);
    if (num < 0) {
        return 'จำนวนตันต้องไม่น้อยกว่า 0';
    }
    return true;
};

const feeValidationRule = (value: string) => {
    if (!value) return true; // Allow empty
    const pattern = /^[0-9]*\.?[0-9]{0,2}$/;
    if (!pattern.test(value)) {
        return 'ราคาต้องเป็นตัวเลข (ทศนิยม 2 ตำแหน่ง)';
    }
    const num = parseFloat(value);
    if (num < 0) {
        return 'ราคาต้องไม่น้อยกว่า 0';
    }
    return true;
};

// Service tons field
const {
    value: serviceTonsInput,
    errorMessage: serviceTonsError
} = useField('serviceTons', serviceTonsValidationRule, {
    initialValue: props.serviceFeetons?.toString() || ''
});

// Service fee per ton field
const {
    value: serviceFeePerTonInput,
    errorMessage: serviceFeePerTonError
} = useField('serviceFeePerTon', feeValidationRule, {
    initialValue: props.serviceFeePerTon?.toString() || ''
});

// Bag fee field
const {
    value: bagFeeInput,
    errorMessage: bagFeeError
} = useField('bagFee', feeValidationRule, {
    initialValue: props.bagFee?.toString() || ''
});

// Transport fee field
const {
    value: transportFeeInput,
    errorMessage: transportFeeError
} = useField('transportFee', feeValidationRule, {
    initialValue: props.transportFee?.toString() || ''
});

// Watch and emit changes
watch(serviceTonsInput, (newVal) => {
    const num = newVal === '' ? 0 : parseInt(newVal) || 0;
    emit('update:serviceFeetons', num);
});

watch(serviceFeePerTonInput, (newVal) => {
    const num = newVal === '' ? 0 : parseFloat(newVal) || 0;
    emit('update:serviceFeePerTon', num);
});

watch(bagFeeInput, (newVal) => {
    const num = newVal === '' ? 0 : parseFloat(newVal) || 0;
    emit('update:bagFee', num);
});

watch(transportFeeInput, (newVal) => {
    const num = newVal === '' ? 0 : parseFloat(newVal) || 0;
    emit('update:transportFee', num);
});
</script>