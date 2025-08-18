<template>
    <div class="space-y-6 p-6">
        <!-- Vehicle Selection Reminder -->
        <div v-if="selectedDeviceIds.length === 0" class="text-center py-12">
            <h3 class="text-lg font-medium text-gray-900 mb-2">กรุณาเลือกรถ</h3>
            <p class="text-gray-500">เลือกรถอย่างน้อย 1 คันเพื่อดูข้อมูลวิเคราะห์</p>
        </div>
        
        <!-- Analytics Content -->
        <div v-else class="space-y-6">
            <!-- Vehicle Utilization Over Time -->
            <UtilizationChart
                :loading="loadingUtilization"
                :utilization-data="utilizationData"
                :selected-device-ids="selectedDeviceIds"
                :devices="devices"
                :vehicle-colors="vehicleColors"
            />
            
            <!-- Future Analytics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="p-4 border rounded-lg bg-gray-50">
                    <h4 class="font-medium text-sm mb-2">ประสิทธิภาพการหยุด</h4>
                    <p class="text-xs text-gray-600">อัตราส่วนการหยุดที่มีประสิทธิภาพ</p>
                </div>
                <div class="p-4 border rounded-lg bg-gray-50">
                    <h4 class="font-medium text-sm mb-2">การเปรียบเทียบประเภทรถ</h4>
                    <p class="text-xs text-gray-600">เปรียบเทียบประสิทธิภาพแต่ละประเภท</p>
                </div>
                <div class="p-4 border rounded-lg bg-gray-50">
                    <h4 class="font-medium text-sm mb-2">ต้นทุนและทรัพยากร</h4>
                    <p class="text-xs text-gray-600">วิเคราะห์ประสิทธิภาพน้ำมัน</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import UtilizationChart from './UtilizationChart.vue';

interface Props {
    selectedDeviceIds: number[];
    devices: any[];
    vehicleColors: string[];
    loadingUtilization: boolean;
    utilizationData: Record<string, any>;
}

defineProps<Props>();
</script>