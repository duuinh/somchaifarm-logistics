<template>
    <div class="mb-6">
        <VeeForm class="bg-blue-50 border-2 border-blue-200 rounded-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-blue-900 flex items-center">
                    <Plus class="mr-2 h-4 w-4" />
                    เพิ่มสินค้าใหม่
                </h4>
                <div class="space-y-2">
                    <Label class="text-xs font-medium text-blue-900">ประเภทราคา *</Label>
                    <div class="flex gap-3">
                        <label class="flex items-center space-x-2">
                            <input
                                :value="pricingType"
                                @change="$emit('update:pricingType', 'regular')"
                                type="radio"
                                :checked="pricingType === 'regular'"
                                class="w-3 h-3 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                            />
                            <span class="text-xs font-medium text-blue-900">ราคาปกติ</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input
                                :value="pricingType"
                                @change="$emit('update:pricingType', 'credit')"
                                type="radio"
                                :checked="pricingType === 'credit'"
                                class="w-3 h-3 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                            />
                            <span class="text-xs font-medium text-blue-900">ราคาเครดิต</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4 space-y-2">
                    <Label class="text-blue-900">เลือกสินค้า *</Label>
                    <Combobox
                        :model-value="itemId"
                        @update:model-value="$emit('update:itemId', $event)"
                        :options="itemOptions"
                        placeholder="พิมพ์เพื่อค้นหาหรือเลือกสินค้า..."
                        class="border-2 border-blue-200 focus:border-blue-400"
                    />
                </div>

                <div class="md:col-span-2 space-y-2">
                    <Label class="text-blue-900">หน่วย</Label>
                    <select
                        :value="unitType"
                        @change="$emit('update:unitType', ($event.target as HTMLSelectElement).value)"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 border-2 border-blue-200 focus:border-blue-400"
                    >
                        <option value="kg">กิโลกรัม (กก.)</option>
                        <option value="bags">กระสอบ</option>
                    </select>
                </div>

                <div class="md:col-span-2 space-y-2">
                    <Label class="text-blue-900">จำนวน *</Label>
                    <VeeField
                        name="quantity"
                        :rules="quantityValidationRule"
                        v-slot="{ field, errorMessage }"
                        :model-value="quantityInput"
                    >
                        <Input
                            v-bind="field"
                            v-model="quantityInput"
                            type="text"
                            inputmode="decimal"
                            :placeholder="unitType === 'kg' ? '0.00 กก.' : '0 กระสอบ'"
                            :class="{
                                'border-2 border-blue-200 focus:border-blue-400': !errorMessage,
                                'border-2 border-red-200 focus:border-red-400 bg-red-50': errorMessage
                            }"
                        />
                        <ErrorMessage name="quantity" class="text-sm text-red-500" />
                    </VeeField>
                </div>

                <div class="md:col-span-2 space-y-2">
                    <Label class="text-blue-900">ราคาต่อหน่วย *</Label>
                    <VeeField
                        name="unitPrice"
                        :rules="unitPriceValidationRule"
                        v-slot="{ field, errorMessage }"
                        :model-value="unitPriceInput"
                    >
                        <Input
                            v-bind="field"
                            v-model="unitPriceInput"
                            type="text"
                            inputmode="decimal"
                            placeholder="0.00 บาท"
                            :class="{
                                'border-2 border-blue-200 focus:border-blue-400': !errorMessage,
                                'border-2 border-red-200 focus:border-red-400 bg-red-50': errorMessage
                            }"
                        />
                        <ErrorMessage name="unitPrice" class="text-sm text-red-500" />
                    </VeeField>
                </div>

                <div class="md:col-span-2">
                    <Button
                        type="button"
                        @click="handleAddItem"
                        :disabled="!canAddItem"
                        class="w-full"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        เพิ่ม
                    </Button>
                </div>
            </div>
            <!-- Item Preview -->
            <div v-if="itemId && selectedItemInfo" class="mt-3 p-3 bg-white rounded-md border border-blue-100">
                <div class="text-sm space-y-1">
                    <div>
                        <span class="text-gray-500">สินค้า:</span>
                        <span class="font-medium ml-1">{{ selectedItemInfo.name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">ราคาปกติ ({{ unitType === 'kg' ? 'กก.' : 'กระสอบ' }}):</span>
                        <span class="font-medium ml-1 text-green-600">
                            {{ unitType === 'kg'
                                ? (selectedItemInfo.regular_price_per_kg?.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) || '-')
                                : (selectedItemInfo.regular_price_per_bag?.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) || '-')
                            }} บาท
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">ราคาเครดิต ({{ unitType === 'kg' ? 'กก.' : 'กระสอบ' }}):</span>
                        <span class="font-medium ml-1 text-orange-600">
                            {{ unitType === 'kg'
                                ? (selectedItemInfo.credit_price_per_kg?.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) || '-')
                                : (selectedItemInfo.credit_price_per_bag?.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) || '-')
                            }} บาท
                        </span>
                    </div>
                </div>
                <div v-if="quantity && quantity > 0 && unitPrice" class="mt-2 pt-2 border-t border-blue-100">
                    <div class="text-sm">
                        <span class="text-gray-500">ราคารวมที่จะได้:</span>
                        <span class="font-bold ml-1 text-green-600 text-lg">
                            {{ (quantity * unitPrice).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                        </span>
                    </div>
                </div>
            </div>
        </VeeForm>
    </div>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-vue-next';
import { Combobox } from '@/components/ui/combobox';
import { computed, ref, watch } from 'vue';
import type { Item, UnitType } from '@/types/delivery-notes';
import { Form as VeeForm, Field as VeeField, ErrorMessage } from 'vee-validate';

interface Props {
    items: Item[];
    itemId: string;
    unitType: UnitType;
    quantity: number | null;
    unitPrice: number | null;
    selectedItemInfo: Item | null;
    pricingType: 'regular' | 'credit';
}

const props = defineProps<Props>();

// Local reactive values for form inputs
const quantityInput = ref(props.quantity?.toString() || '');
const unitPriceInput = ref(props.unitPrice?.toString() || '');

// Validation rules for VeeValidate (isolated within VeeForm)
const quantityValidationRule = computed(() => {
    const pattern = props.unitType === 'kg' ? /^[0-9]*\.?[0-9]{0,2}$/ : /^[0-9]*$/;
    return (value: string) => {
        if (!value) return true; // Allow empty - only validate when user wants to add
        if (!pattern.test(value)) {
            return props.unitType === 'kg' 
                ? 'จำนวนต้องเป็นตัวเลข (ทศนิยม 2 ตำแหน่ง)' 
                : 'จำนวนต้องเป็นจำนวนเต็ม';
        }
        const num = parseFloat(value);
        if (num <= 0) {
            return 'จำนวนต้องมากกว่า 0';
        }
        if (props.unitType === 'bags' && !Number.isInteger(num)) {
            return 'จำนวนกระสอบต้องเป็นจำนวนเต็ม';
        }
        return true;
    };
});

const unitPriceValidationRule = (value: string) => {
    if (!value) return true; // Allow empty - only validate when user wants to add
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

// Watch for prop changes to update field values
watch(() => props.quantity, (newVal) => {
    quantityInput.value = newVal?.toString() || '';
}, { immediate: true });

watch(() => props.unitPrice, (newVal) => {
    unitPriceInput.value = newVal?.toString() || '';
}, { immediate: true });

// Clear inputs when unit type changes
watch(() => props.unitType, () => {
    quantityInput.value = '';
});

const emit = defineEmits<{
    'update:itemId': [value: string];
    'update:unitType': [value: UnitType];
    'update:quantity': [value: number | null];
    'update:unitPrice': [value: number | null];
    'update:pricingType': [value: 'regular' | 'credit'];
    addItem: [];
}>();

// Watch field values and emit changes
watch(quantityInput, (newVal) => {
    const num = newVal === '' ? null : parseFloat(newVal) || null;
    emit('update:quantity', num);
});

watch(unitPriceInput, (newVal) => {
    const num = newVal === '' ? null : parseFloat(newVal) || null;
    emit('update:unitPrice', num);
});

// Auto-set price based on pricing type when item or unit type changes
watch([() => props.itemId, () => props.unitType, () => props.pricingType], () => {
    if (props.selectedItemInfo && props.itemId) {
        let price: number | null = null;
        
        if (props.unitType === 'kg') {
            price = props.pricingType === 'credit' 
                ? props.selectedItemInfo.credit_price_per_kg 
                : props.selectedItemInfo.regular_price_per_kg;
        } else {
            price = props.pricingType === 'credit' 
                ? props.selectedItemInfo.credit_price_per_bag 
                : props.selectedItemInfo.regular_price_per_bag;
        }
        
        if (price !== null && price !== undefined) {
            unitPriceInput.value = price.toString();
            emit('update:unitPrice', price);
        }
    }
});

// Item options for combobox
const itemOptions = computed(() => {
    return props.items.map(item => ({
        value: item.id.toString(),
        label: `${String(item.id).padStart(3, '0')} - ${item.name}`
    }));
});

// Check if form can be submitted
const canAddItem = computed(() => {
    return props.itemId && 
           props.quantity && 
           props.quantity > 0 && 
           props.unitPrice !== null && 
           props.unitPrice >= 0;
});

// Handle add item 
const handleAddItem = () => {
    if (canAddItem.value) {
        emit('addItem');
    }
};

</script>