<template>
    <div class="space-y-6 p-6">
        <!-- Vehicle Selection Reminder -->
        <div v-if="effectiveSelectedIds.length === 0" class="text-center py-12">
            <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่มีรถในระบบ</h3>
            <p class="text-gray-500">ไม่พบข้อมูลรถสำหรับการวิเคราะห์</p>
        </div>
        
        <!-- Analytics Content -->
        <div v-else class="space-y-6">
            <!-- Time Period Controls -->
            <div class="flex flex-wrap gap-4 items-center p-4 bg-gray-50 rounded-lg">
                <span class="text-sm font-medium text-gray-700">ช่วงเวลา:</span>
                
                <!-- Quick Period Buttons -->
                <div class="flex gap-1">
                    <Button 
                        @click="selectQuickPeriod(7)"
                        :variant="selectedPeriod === 7 && !customDateRange ? 'default' : 'outline'"
                        size="sm"
                        class="h-7 px-3 text-xs"
                    >
                        7 วัน
                    </Button>
                    <Button 
                        @click="selectQuickPeriod(30)"
                        :variant="selectedPeriod === 30 && !customDateRange ? 'default' : 'outline'"
                        size="sm"
                        class="h-7 px-3 text-xs"
                    >
                        30 วัน
                    </Button>
                </div>
                
                <!-- Custom Date Range -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">หรือเลือกช่วง:</span>
                    <input
                        type="date"
                        v-model="startDate"
                        :max="endDate"
                        class="h-7 px-2 text-xs border border-gray-300 rounded"
                    />
                    <span class="text-xs text-gray-500">ถึง</span>
                    <input
                        type="date"
                        v-model="endDate"
                        :min="startDate"
                        :max="today"
                        class="h-7 px-2 text-xs border border-gray-300 rounded"
                    />
                    <Button 
                        @click="applyCustomDateRange"
                        :variant="customDateRange ? 'default' : 'outline'"
                        size="sm"
                        class="h-7 px-2 text-xs"
                    >
                        ใช้
                    </Button>
                </div>
            </div>
            
            <!-- Charts Row - Wider utilization chart -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Vehicle Utilization Over Time -->
                <div class="lg:col-span-2">
                    <UtilizationChart
                    :loading="loadingUtilization"
                    :utilization-data="utilizationData"
                    :selected-device-ids="effectiveSelectedIds"
                    :devices="devices"
                    :selected-period="selectedPeriod"
                    :get-vehicle-color="getVehicleColor"
                    :show-legend="false"
                    :hidden-vehicles="hiddenVehicles"
                    :hidden-vehicle-types="hiddenVehicleTypes"
                    />
                </div>
                
                <!-- Vehicle Type Comparison -->
                <VehicleTypeComparison
                    :loading="loadingUtilization"
                    :utilization-data="utilizationData"
                    :devices="devices"
                    :get-vehicle-color="getVehicleColor"
                    :show-legend="false"
                />
            </div>
            
            <!-- Shared Legend (matching UtilizationChart style) -->
            <div class="space-y-4 pt-6 border-t">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">สรุปอัตราการใช้งานแยกตามประเภท</h3>
                    <div class="text-sm text-gray-500">
                        {{ vehicleTypeGroups.length }} ประเภท • {{ devices.length }} คัน • ข้อมูล {{ customDateRange ? 'ช่วงที่เลือก' : selectedPeriod + ' วันย้อนหลัง' }}
                    </div>
                </div>
                
                <!-- Type Filter Buttons -->
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs font-medium text-gray-600 self-center">เลือกดูประเภท:</span>
                    <button v-for="typeGroup in vehicleTypeGroups" 
                        :key="typeGroup.type"
                        @click="toggleVehicleType(typeGroup.type)"
                        class="flex items-center gap-1 px-3 py-1 text-xs rounded-full border transition-all"
                        :class="hiddenVehicleTypes.includes(typeGroup.type) 
                            ? 'bg-gray-100 text-gray-400 border-gray-200 opacity-50' 
                            : 'bg-white text-gray-700 border-gray-300 hover:border-blue-300 hover:bg-blue-50'"
                    >
                        <div 
                            class="w-2 h-2 rounded-full"
                            :style="{ backgroundColor: hiddenVehicleTypes.includes(typeGroup.type) ? '#9ca3af' : getUtilizationColor(typeGroup.avgUtilization) }"
                        ></div>
                        <span>{{ typeGroup.type }} ({{ typeGroup.avgUtilization }}%)</span>
                    </button>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <div v-for="typeGroup in vehicleTypeGroups" 
                        :key="typeGroup.type"
                        class="border rounded-lg transition-all flex-1 min-w-64"
                        :class="hiddenVehicleTypes.includes(typeGroup.type) 
                            ? 'bg-gray-50 opacity-50' 
                            : 'bg-white hover:shadow-sm'"
                    >
                        <!-- Type Header -->
                        <div class="p-3 border-b bg-gray-50">
                            <div class="flex items-center justify-between">
                                <button 
                                    @click="toggleVehicleType(typeGroup.type)"
                                    class="flex items-center gap-2 hover:opacity-75"
                                >
                                    <div 
                                        class="w-3 h-3 rounded"
                                        :style="{ backgroundColor: hiddenVehicleTypes.includes(typeGroup.type) ? '#9ca3af' : getUtilizationColor(typeGroup.avgUtilization) }"
                                    ></div>
                                    <span class="text-sm font-medium">{{ typeGroup.type }}</span>
                                </button>
                                <span class="text-lg font-bold" :style="{ color: hiddenVehicleTypes.includes(typeGroup.type) ? '#9ca3af' : getUtilizationColor(typeGroup.avgUtilization) }">
                                    {{ typeGroup.avgUtilization }}%
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ typeGroup.vehicles.length }} คัน • สูงสุด {{ typeGroup.maxUtilization }}% • ต่ำสุด {{ typeGroup.minUtilization }}%
                            </div>
                        </div>
                        
                        <!-- Individual Vehicles -->
                        <div class="p-2" v-if="!hiddenVehicleTypes.includes(typeGroup.type)">
                            <div class="flex flex-wrap gap-1">
                                <button v-for="vehicle in typeGroup.vehicles" 
                                        :key="vehicle.id"
                                        @click="toggleVehicle(vehicle.id)"
                                        class="flex items-center gap-1 text-xs hover:opacity-75 transition-opacity px-2 py-1 rounded border"
                                        :class="hiddenVehicles.includes(vehicle.id) 
                                            ? 'opacity-50 line-through bg-gray-100 border-gray-200' 
                                            : 'bg-white border-gray-200 hover:border-blue-300'"
                                        :title="`${vehicle.name}: Click เพื่อซ่อน/แสดงในกราฟ`"
                                >
                                    <div 
                                        class="w-2 h-2 rounded"
                                        :style="{ backgroundColor: getVehicleColor(vehicle.id) }"
                                    ></div>
                                    <span>{{ vehicle.shortname || vehicle.name }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import UtilizationChart from './UtilizationChart.vue';
import VehicleTypeComparison from './VehicleTypeComparison.vue';

interface Props {
    selectedDeviceIds: number[];
    devices: any[];
    loadingUtilization: boolean;
    utilizationData: Record<string, any>;
    getVehicleColor: (deviceId: number) => string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'period-change': [days: number];
    'date-range-change': [startDate: string, endDate: string];
}>();

// Use all vehicles by default if none selected
const effectiveSelectedIds = computed(() => {
    return props.selectedDeviceIds.length > 0 
        ? props.selectedDeviceIds 
        : props.devices.map(d => d.id);
});

// Filter state for shared legend
const hiddenVehicles = ref<number[]>([]);
const hiddenVehicleTypes = ref<string[]>([]);

// Toggle functions
const toggleVehicle = (vehicleId: number) => {
    const index = hiddenVehicles.value.indexOf(vehicleId);
    if (index > -1) {
        hiddenVehicles.value.splice(index, 1);
    } else {
        hiddenVehicles.value.push(vehicleId);
    }
};

const toggleVehicleType = (vehicleType: string) => {
    const index = hiddenVehicleTypes.value.indexOf(vehicleType);
    if (index > -1) {
        hiddenVehicleTypes.value.splice(index, 1);
    } else {
        hiddenVehicleTypes.value.push(vehicleType);
    }
};

// Group vehicles by type for shared legend
const vehicleTypeGroups = computed(() => {
    const typeGroups = new Map<string, any[]>();
    
    props.devices.forEach(vehicle => {
        if (!typeGroups.has(vehicle.type)) {
            typeGroups.set(vehicle.type, []);
        }
        typeGroups.get(vehicle.type)?.push(vehicle);
    });
    
    return Array.from(typeGroups.entries()).map(([typeName, vehicles]) => {
        // Calculate average utilization for this type
        const typeUtilizations: number[] = [];
        Object.values(props.utilizationData).forEach(dayData => {
            vehicles.forEach(vehicle => {
                const utilization = dayData[vehicle.id] || 0;
                if (utilization > 0) {
                    typeUtilizations.push(utilization);
                }
            });
        });
        
        const avgUtilization = typeUtilizations.length > 0 
            ? Math.round(typeUtilizations.reduce((a, b) => a + b, 0) / typeUtilizations.length)
            : 0;
        
        const maxUtilization = typeUtilizations.length > 0 ? Math.max(...typeUtilizations) : 0;
        const minUtilization = typeUtilizations.length > 0 ? Math.min(...typeUtilizations) : 0;
        
        return {
            type: typeName,
            vehicles,
            avgUtilization,
            maxUtilization,
            minUtilization
        };
    }).sort((a, b) => b.avgUtilization - a.avgUtilization);
});

// Get color based on utilization performance
const getUtilizationColor = (utilization: number) => {
    if (utilization >= 60) return '#22c55e'; // Green - Excellent
    if (utilization >= 40) return '#3b82f6'; // Blue - Good  
    if (utilization >= 25) return '#f59e0b'; // Yellow - Fair
    if (utilization >= 15) return '#f97316'; // Orange - Poor
    return '#ef4444'; // Red - Very Poor
};

// Time control state
const selectedPeriod = ref(7);
const customDateRange = ref(false);

// Date range state
const today = new Date().toISOString().split('T')[0];
const startDate = ref('');
const endDate = ref(today);

// Initialize date range based on default period
const initializeDateRange = () => {
    const end = new Date();
    const start = new Date();
    start.setDate(start.getDate() - selectedPeriod.value + 1);
    
    startDate.value = start.toISOString().split('T')[0];
    endDate.value = end.toISOString().split('T')[0];
};

// Initialize on mount
initializeDateRange();

// Quick period selection
const selectQuickPeriod = (days: number) => {
    selectedPeriod.value = days;
    customDateRange.value = false;
    initializeDateRange();
    emit('period-change', days);
};

// Apply custom date range
const applyCustomDateRange = () => {
    if (startDate.value && endDate.value) {
        customDateRange.value = true;
        emit('date-range-change', startDate.value, endDate.value);
    }
};
</script>