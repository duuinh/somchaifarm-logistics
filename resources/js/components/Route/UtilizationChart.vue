<template>
    <Card>
        <CardHeader>
            <CardTitle class="text-base">อัตราการใช้งานรถย้อนหลัง 7 วัน</CardTitle>
            <CardDescription class="text-xs">เปอร์เซ็นต์การเคลื่อนที่ในเวลาทำงาน (หักเวลาพักกลางวัน)</CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="h-8 w-8 animate-spin text-gray-400" />
                <span class="ml-2 text-gray-600">กำลังโหลดข้อมูล...</span>
            </div>
            
            <div v-else-if="Object.keys(utilizationData).length > 0" class="space-y-4">
                <!-- Chart Container -->
                <div class="overflow-x-auto">
                    <div class="min-w-[600px]">
                        <!-- Y-axis labels and bars -->
                        <div class="space-y-2">
                            <template v-for="vehicle in selectedVehicles" :key="vehicle.id">
                                <div class="flex items-center gap-2">
                                    <!-- Vehicle name -->
                                    <div class="w-32 text-xs truncate" :title="vehicle.name">
                                        {{ vehicle.name.replace(/^🚛\s*|^🚚\s*/, '') }}
                                    </div>
                                    
                                    <!-- Bar chart for this vehicle -->
                                    <div class="flex-1 flex gap-1">
                                        <template v-for="(dateData, date) in utilizationData" :key="date">
                                            <div class="flex-1 flex flex-col items-center">
                                                <div class="w-full bg-gray-200 rounded-t h-24 flex flex-col justify-end">
                                                    <div 
                                                        :style="{ 
                                                            height: `${(dateData[vehicle.id] || 0)}%`,
                                                            backgroundColor: getVehicleColor(vehicle.id)
                                                        }"
                                                        class="w-full rounded-t transition-all duration-300"
                                                        :title="`${dateData[vehicle.id] || 0}%`"
                                                    >
                                                        <div class="text-xs text-white text-center font-medium pt-1">
                                                            {{ dateData[vehicle.id] || 0 }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- X-axis labels (dates) -->
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-32"></div>
                            <div class="flex-1 flex gap-1">
                                <template v-for="(dateData, date) in utilizationData" :key="date">
                                    <div class="flex-1 text-xs text-center text-gray-600">
                                        {{ new Date(date + 'T00:00:00').toLocaleDateString('th-TH', { day: 'numeric', month: 'short' }) }}
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="flex items-center gap-4 text-xs text-gray-600 pt-4 border-t">
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-green-500 rounded"></div>
                        <span>&gt; 30% ดีมาก</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-orange-500 rounded"></div>
                        <span>15-30% ปานกลาง</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-red-500 rounded"></div>
                        <span>&lt; 15% ต่ำ</span>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
                ไม่มีข้อมูลสำหรับการวิเคราะห์
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Loader2 } from 'lucide-vue-next';

interface Vehicle {
    id: number;
    name: string;
    type: string;
}

interface Props {
    loading: boolean;
    utilizationData: Record<string, Record<number, number>>;
    selectedDeviceIds: number[];
    devices: Vehicle[];
    vehicleColors: string[];
}

const props = defineProps<Props>();

// Get selected vehicles
const selectedVehicles = computed(() => {
    return props.devices.filter(d => props.selectedDeviceIds.includes(d.id));
});

// Get vehicle color
const getVehicleColor = (deviceId: number) => {
    const index = props.selectedDeviceIds.indexOf(deviceId);
    return index >= 0 ? props.vehicleColors[index % props.vehicleColors.length] : '#0000FF';
};
</script>