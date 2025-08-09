<template>
    <div class="mb-6">
        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-5">
            <h4 class="text-sm font-semibold text-blue-900 mb-4 flex items-center">
                <Plus class="mr-2 h-4 w-4" />
                เพิ่มสินค้าใหม่
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4 space-y-2">
                    <Label class="text-blue-900">เลือกสินค้า *</Label>
                    <select
                        :value="itemId"
                        @change="$emit('update:itemId', $event.target.value)"
                        class="flex h-10 w-full items-center justify-between whitespace-nowrap rounded-md border-2 border-blue-200 bg-white px-3 py-2 text-sm shadow-sm hover:border-blue-300 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1"
                    >
                        <option value="">-- เลือกสินค้า --</option>
                        <option v-for="item in items" :key="item.id" :value="item.id">
                            {{ item.name }} ({{ item.kg_per_bag_conversion }} กก./กระสอบ)
                        </option>
                    </select>
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
                    <Label class="text-blue-900">จำนวน</Label>
                    <Input
                        :model-value="quantity"
                        @update:model-value="$emit('update:quantity', $event)"
                        type="number"
                        step="1"
                        min="0"
                        :placeholder="unitType === 'kg' ? '0 กก.' : '0 กระสอบ'"
                        class="border-2 border-green-200 focus:border-green-400 bg-green-50"
                    />
                </div>

                <div class="md:col-span-2 space-y-2">
                    <Label class="text-blue-900">ราคาต่อหน่วย</Label>
                    <Input
                        :model-value="unitPrice"
                        @update:model-value="$emit('update:unitPrice', $event)"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00 บาท"
                        class="border-2 border-blue-200 focus:border-blue-400"
                    />
                </div>

                <div class="md:col-span-2">
                    <Button
                        type="button"
                        @click="$emit('addItem')"
                        :disabled="!itemId || !quantity || unitPrice === null"
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
                                ? (selectedItemInfo.regular_price_per_kg?.toLocaleString() || '-')
                                : (selectedItemInfo.regular_price_per_bag?.toLocaleString() || '-')
                            }} บาท
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">ราคาเครดิต ({{ unitType === 'kg' ? 'กก.' : 'กระสอบ' }}):</span>
                        <span class="font-medium ml-1 text-orange-600">
                            {{ unitType === 'kg'
                                ? (selectedItemInfo.credit_price_per_kg?.toLocaleString() || '-')
                                : (selectedItemInfo.credit_price_per_bag?.toLocaleString() || '-')
                            }} บาท
                        </span>
                    </div>
                </div>
                <div v-if="quantity && quantity > 0 && unitPrice" class="mt-2 pt-2 border-t border-blue-100">
                    <div class="text-sm">
                        <span class="text-gray-500">ราคารวมที่จะได้:</span>
                        <span class="font-bold ml-1 text-green-600 text-lg">
                            {{ (quantity * unitPrice).toLocaleString() }} บาท
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
import type { Item, UnitType } from '@/types/delivery-notes';

interface Props {
    items: Item[];
    itemId: string;
    unitType: UnitType;
    quantity: number | null;
    unitPrice: number | null;
    selectedItemInfo: Item | null;
}

defineProps<Props>();
defineEmits<{
    'update:itemId': [value: string];
    'update:unitType': [value: UnitType];
    'update:quantity': [value: number | null];
    'update:unitPrice': [value: number | null];
    addItem: [];
}>();
</script>