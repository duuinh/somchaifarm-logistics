<template>
    <div v-if="items.length > 0" class="mb-6">
        <div class="border-2 border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700">
                    รายการสินค้าที่เลือก ({{ items.length }} รายการ)
                </h4>
            </div>
            <Table>
                <TableHeader>
                    <TableRow class="bg-gray-50">
                        <TableHead class="font-semibold">#</TableHead>
                        <TableHead class="font-semibold">รายการสินค้า</TableHead>
                        <TableHead class="text-center font-semibold">จำนวน</TableHead>
                        <TableHead class="text-center font-semibold">หน่วย</TableHead>
                        <TableHead class="text-right font-semibold">ราคาต่อหน่วย (บาท)</TableHead>
                        <TableHead class="text-right font-semibold">ราคารวม</TableHead>
                        <TableHead class="w-20"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(item, index) in items" :key="index" class="hover:bg-gray-50">
                        <TableCell class="font-medium text-gray-500">{{ index + 1 }}</TableCell>
                        <TableCell class="font-medium">{{ getItemName(item.item_id) }}</TableCell>
                        <TableCell class="text-center">
                            <span class="text-lg font-semibold">
                                {{ item.quantity }}
                            </span>
                            <div v-if="item.unit_type === 'bags' && getKgPerBag(item.item_id)" class="text-xs text-gray-500 mt-1">
                                ({{ getKgPerBag(item.item_id) }} กก./กระสอบ)
                            </div>
                        </TableCell>
                        <TableCell class="text-center">
                            <span v-if="item.unit_type === 'kg'" class="inline-flex items-center px-2 py-1 rounded-md bg-purple-50 text-purple-700 text-sm font-medium">
                                กิโลกรัม
                            </span>
                            <span v-if="item.unit_type === 'bags'" class="inline-flex items-center px-2 py-1 rounded-md bg-green-50 text-green-700 text-sm font-medium">
                                กระสอบ
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <span class="font-medium text-green-600">
                                {{ item.price_per_unit.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right font-semibold text-blue-600">
                            {{ item.total_price.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท
                        </TableCell>
                        <TableCell>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="$emit('removeItem', index)"
                                class="hover:bg-red-50"
                            >
                                <Trash2 class="h-4 w-4 text-red-500" />
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
    <div v-else class="mb-6 text-center py-8 text-muted-foreground">
        ยังไม่มีรายการสินค้า กรุณาเพิ่มสินค้า
    </div>
</template>

<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Trash2 } from 'lucide-vue-next';
import type { DeliveryNoteItem } from '@/types/delivery-notes';

interface Props {
    items: DeliveryNoteItem[];
    getItemName: (itemId: number) => string;
    allItems?: Item[];
}

interface Item {
    id: number;
    name: string;
    kg_per_bag_conversion: number;
}

const props = defineProps<Props>();
defineEmits<{
    removeItem: [index: number];
}>();

const getKgPerBag = (itemId: number): number | null => {
    if (!props.allItems) return null;
    const item = props.allItems.find(i => i.id === itemId);
    return item?.kg_per_bag_conversion || null;
};
</script>