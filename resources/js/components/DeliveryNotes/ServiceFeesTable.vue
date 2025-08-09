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
                            <Input
                                :model-value="serviceFeetons"
                                @update:model-value="$emit('update:serviceFeetons', Number($event))"
                                type="number"
                                step="1"
                                min="0"
                                placeholder="0"
                                class="w-24 text-center"
                                :disabled="!includeServiceFee"
                            />
                        </div>
                    </TableCell>
                    <TableCell class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-orange-50 text-orange-700 text-sm font-medium">
                            ตัน
                        </span>
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Input
                                :model-value="serviceFeePerTon"
                                @update:model-value="$emit('update:serviceFeePerTon', Number($event))"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="300"
                                class="w-24 text-right"
                                :disabled="!includeServiceFee"
                            />
                            <span class="text-sm text-gray-500">บาท/ตัน</span>
                        </div>
                    </TableCell>
                    <TableCell class="text-right font-semibold text-blue-600">
                        {{ includeServiceFee ? Number(calculatedServiceFee).toLocaleString() : '0' }} บาท
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
                            <Input
                                :model-value="bagFee"
                                @update:model-value="$emit('update:bagFee', Number($event))"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0"
                                class="w-24 text-center"
                                :disabled="!includeBagFee"
                            />
                        </div>
                    </TableCell>
                    <TableCell class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-medium">
                            บาท
                        </span>
                    </TableCell>
                    <TableCell class="text-right">-</TableCell>
                    <TableCell class="text-right font-semibold text-blue-600">
                        {{ includeBagFee ? Number(bagFee || 0).toLocaleString() : '0' }} บาท
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
                            <Input
                                :model-value="transportFee"
                                @update:model-value="$emit('update:transportFee', Number($event))"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0"
                                class="w-24 text-center"
                                :disabled="!includeTransportFee"
                            />
                        </div>
                    </TableCell>
                    <TableCell class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-medium">
                            บาท
                        </span>
                    </TableCell>
                    <TableCell class="text-right">-</TableCell>
                    <TableCell class="text-right font-semibold text-blue-600">
                        {{ includeTransportFee ? Number(transportFee || 0).toLocaleString() : '0' }} บาท
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

defineProps<Props>();
defineEmits<{
    'update:includeServiceFee': [value: boolean];
    'update:includeBagFee': [value: boolean];
    'update:includeTransportFee': [value: boolean];
    'update:serviceFeetons': [value: number];
    'update:serviceFeePerTon': [value: number];
    'update:bagFee': [value: number];
    'update:transportFee': [value: number];
}>();
</script>