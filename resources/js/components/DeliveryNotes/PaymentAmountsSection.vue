<template>
    <div class="mb-6 border-2 border-green-200 rounded-lg overflow-hidden">
        <div class="bg-green-50 px-4 py-2 border-b border-green-200">
            <h4 class="text-sm font-semibold text-green-700">จำนวนเงิน</h4>
        </div>
        <div class="p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Cash Amount -->
                <div class="space-y-2">
                    <Label for="cash_amount" class="text-green-700 font-medium">เงินสด (บาท)</Label>
                    <VeeField
                        name="cash_amount"
                        :rules="cashAmountValidationRule"
                        v-slot="{ field, errorMessage }"
                        :model-value="cashAmount?.toString() || ''"
                    >
                        <Input
                            v-bind="field"
                            v-model="cashAmountInput"
                            type="text"
                            inputmode="decimal"
                            placeholder="0.00"
                            :class="{
                                'border-2 border-green-200 focus:border-green-400': !errorMessage,
                                'border-2 border-red-200 focus:border-red-400 bg-red-50': errorMessage
                            }"
                        />
                        <ErrorMessage name="cash_amount" class="text-sm text-red-500" />
                    </VeeField>
                </div>

                <!-- Transfer Amount -->
                <div class="space-y-2">
                    <Label for="transfer_amount" class="text-green-700 font-medium">เงินโอน (บาท)</Label>
                    <VeeField
                        name="transfer_amount"
                        :rules="transferAmountValidationRule"
                        v-slot="{ field, errorMessage }"
                        :model-value="transferAmount?.toString() || ''"
                    >
                        <Input
                            v-bind="field"
                            v-model="transferAmountInput"
                            type="text"
                            inputmode="decimal"
                            placeholder="0.00"
                            :class="{
                                'border-2 border-green-200 focus:border-green-400': !errorMessage,
                                'border-2 border-red-200 focus:border-red-400 bg-red-50': errorMessage
                            }"
                        />
                        <ErrorMessage name="transfer_amount" class="text-sm text-red-500" />
                    </VeeField>
                </div>
            </div>

            <!-- Payment Summary and Validation -->
            <div v-if="totalPaymentAmount > 0 || totalAmount > 0" class="mt-4 space-y-3">
                <!-- Payment Breakdown -->
                <div v-if="totalPaymentAmount > 0" class="p-3 bg-green-50 rounded-md border border-green-200">
                    <div class="text-sm space-y-1">
                        <div class="flex justify-between">
                            <span class="text-green-700">เงินสด:</span>
                            <span class="font-medium text-green-800">
                                {{ Number(cashAmount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-700">เงินโอน:</span>
                            <span class="font-medium text-green-800">
                                {{ Number(transferAmount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                            </span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-green-300">
                            <span class="text-green-700 font-semibold">รวมเงินที่ได้รับ:</span>
                            <span class="font-bold text-green-800 text-lg">
                                {{ totalPaymentAmount.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Validation -->
                <div v-if="totalAmount > 0" class="p-3 rounded-md border" :class="{
                    'bg-green-50 border-green-200': paymentMatch.isExact,
                    'bg-yellow-50 border-yellow-200': paymentMatch.isOverpaid,
                    'bg-red-50 border-red-200': paymentMatch.isUnderpaid,
                    'bg-gray-50 border-gray-200': totalPaymentAmount === 0
                }">
                    <div class="text-sm space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-700">ยอดรวมใบส่งของ:</span>
                            <span class="font-medium text-gray-800">
                                {{ Number(totalAmount).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-300">
                            <span class="font-semibold" :class="{
                                'text-green-700': paymentMatch.isExact,
                                'text-yellow-700': paymentMatch.isOverpaid,
                                'text-red-700': paymentMatch.isUnderpaid,
                                'text-gray-700': totalPaymentAmount === 0
                            }">สถานะการชำระ:</span>
                            <div class="text-right">
                                <div v-if="paymentMatch.isExact" class="flex items-center text-green-700 font-semibold">
                                    <span class="mr-1">✓</span>
                                    <span>ครบถ้วน</span>
                                </div>
                                <div v-else-if="paymentMatch.isOverpaid" class="text-yellow-700 font-semibold">
                                    <div>เกินจ่าย</div>
                                    <div class="text-xs">
                                        +{{ paymentMatch.difference.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                                    </div>
                                </div>
                                <div v-else-if="paymentMatch.isUnderpaid" class="text-red-700 font-semibold">
                                    <div>ขาด</div>
                                    <div class="text-xs">
                                        -{{ paymentMatch.difference.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                                    </div>
                                </div>
                                <div v-else class="text-gray-500">
                                    ยังไม่ได้ระบุ
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, watch, computed } from 'vue';
import { Field as VeeField, ErrorMessage } from 'vee-validate';

interface Props {
    cashAmount: number | null;
    transferAmount: number | null;
    totalAmount: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:cashAmount': [value: number];
    'update:transferAmount': [value: number];
}>();

// Local reactive values for form inputs
const cashAmountInput = ref(props.cashAmount?.toString() || '');
const transferAmountInput = ref(props.transferAmount?.toString() || '');

// Validation rules for payment amounts
const cashAmountValidationRule = (value: string) => {
    if (!value) return true; // Allow empty
    const pattern = /^[0-9]*\.?[0-9]{0,2}$/;
    if (!pattern.test(value)) {
        return 'จำนวนเงินต้องเป็นตัวเลข (ทศนิยม 2 ตำแหน่ง)';
    }
    const num = parseFloat(value);
    if (num < 0) {
        return 'จำนวนเงินต้องไม่น้อยกว่า 0';
    }
    return true;
};

const transferAmountValidationRule = (value: string) => {
    if (!value) return true; // Allow empty
    const pattern = /^[0-9]*\.?[0-9]{0,2}$/;
    if (!pattern.test(value)) {
        return 'จำนวนเงินต้องเป็นตัวเลข (ทศนิยม 2 ตำแหน่ง)';
    }
    const num = parseFloat(value);
    if (num < 0) {
        return 'จำนวนเงินต้องไม่น้อยกว่า 0';
    }
    return true;
};

// Watch for prop changes to update field values
watch(() => props.cashAmount, (newVal) => {
    cashAmountInput.value = newVal?.toString() || '';
}, { immediate: true });

watch(() => props.transferAmount, (newVal) => {
    transferAmountInput.value = newVal?.toString() || '';
}, { immediate: true });

// Watch field values and emit changes
watch(cashAmountInput, (newVal) => {
    const num = newVal === '' ? 0 : parseFloat(newVal) || 0;
    emit('update:cashAmount', num);
});

watch(transferAmountInput, (newVal) => {
    const num = newVal === '' ? 0 : parseFloat(newVal) || 0;
    emit('update:transferAmount', num);
});

// Computed total payment amount
const totalPaymentAmount = computed(() => {
    return (props.cashAmount || 0) + (props.transferAmount || 0);
});

// Check if payment amounts match total
const paymentMatch = computed(() => {
    const totalPayments = totalPaymentAmount.value;
    const totalAmount = props.totalAmount || 0;
    const difference = Math.abs(totalPayments - totalAmount);
    return {
        isExact: difference < 0.01, // Account for floating point precision
        difference: difference,
        isOverpaid: totalPayments > totalAmount,
        isUnderpaid: totalPayments < totalAmount && totalPayments > 0
    };
});
</script>