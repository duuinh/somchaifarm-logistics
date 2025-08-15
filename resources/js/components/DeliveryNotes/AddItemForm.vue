<template>
    <div class="mb-6">
        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-5">
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
                        @change="$emit('update:unitType', $event.target.value)"
                        class="flex h-10 w-full items-center justify-between whitespace-nowrap rounded-md border-2 border-blue-200 bg-white px-3 py-2 text-sm shadow-sm hover:border-blue-300 focus:border-blue-400"
                    >
                        <option value="kg">กิโลกรัม</option>
                        <option value="bags">กระสอบ</option>
                    </select>
                </div>

                <div class="md:col-span-2 space-y-2">
                    <Label class="text-blue-900">จำนวน *</Label>
                    <Input
                        v-model="quantityInput"
                        type="text"
                        inputmode="decimal"
                        :placeholder="unitType === 'kg' ? '0.00 กก.' : '0 กระสอบ'"
                        :class="{
                            'border-2 border-green-200 focus:border-green-400 bg-green-50': !quantityError,
                            'border-2 border-red-200 focus:border-red-400 bg-red-50': quantityError
                        }"
                        required
                    />
                    <div v-if="quantityError" class="text-sm text-red-500">
                        {{ quantityError }}
                    </div>
                </div>

                <div class="md:col-span-2 space-y-2">
                    <Label class="text-blue-900">ราคาต่อหน่วย *</Label>
                    <Input
                        v-model="unitPriceInput"
                        type="text"
                        inputmode="decimal"
                        placeholder="0.00 บาท"
                        :class="{
                            'border-2 border-blue-200 focus:border-blue-400': !unitPriceError,
                            'border-2 border-red-200 focus:border-red-400 bg-red-50': unitPriceError
                        }"
                        required
                    />
                    <div v-if="unitPriceError" class="text-sm text-red-500">
                        {{ unitPriceError }}
                    </div>
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
                <div class="grid grid-cols-2 gap-4 text-sm">
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
        </div>
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
import { useField } from 'vee-validate';

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

// VeeValidate field for quantity with dynamic validation
const quantityValidationRule = computed(() => {
    const pattern = props.unitType === 'kg' ? /^[0-9]*\.?[0-9]{0,2}$/ : /^[0-9]*$/;
    return (value: string) => {
        if (!value) return true; // Allow empty
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

const {
    value: quantityInput,
    errorMessage: quantityError,
    setValue: setQuantityValue
} = useField('quantity', quantityValidationRule, {
    initialValue: props.quantity?.toString() || ''
});

// Watch for prop changes to update field value
watch(() => props.quantity, (newVal) => {
    setQuantityValue(newVal?.toString() || '');
}, { immediate: true });

// Watch field value and emit changes
watch(quantityInput, (newVal) => {
    const num = newVal === '' ? null : parseFloat(newVal) || null;
    emit('update:quantity', num);
});

const itemOptions = computed(() => {
    return props.items.map(item => ({
        value: item.id.toString(),
        label: `${String(item.id).padStart(3, '0')} - ${item.name} (${item.kg_per_bag_conversion} กก./กระสอบ)`
    }));
});

const emit = defineEmits<{
    'update:itemId': [value: string];
    'update:unitType': [value: UnitType];
    'update:quantity': [value: number | null];
    'update:unitPrice': [value: number | null];
    'update:pricingType': [value: 'regular' | 'credit'];
    addItem: [];
}>();

// VeeValidate field for unit price
const unitPriceValidationRule = (value: string) => {
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

const {
    value: unitPriceInput,
    errorMessage: unitPriceError,
    setValue: setUnitPriceValue
} = useField('unitPrice', unitPriceValidationRule, {
    initialValue: props.unitPrice?.toString() || ''
});

// Watch for prop changes to update field value
watch(() => props.unitPrice, (newVal) => {
    setUnitPriceValue(newVal?.toString() || '');
}, { immediate: true });

// Watch field value and emit changes
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
            setUnitPriceValue(price.toString());
            emit('update:unitPrice', price);
        }
    }
});

// Check if form can be submitted
const canAddItem = computed(() => {
    return props.itemId && 
           props.quantity && 
           props.quantity > 0 && 
           props.unitPrice !== null && 
           props.unitPrice >= 0 && 
           !quantityError.value && 
           !unitPriceError.value;
});

// Handle add item with validation
const handleAddItem = () => {
    if (canAddItem.value) {
        emit('addItem');
    }
};


</script>