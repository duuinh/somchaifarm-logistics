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
            
            <!-- Charts Row - 2x1 Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Vehicle Utilization Over Time -->
                <UtilizationChart
                    :loading="loadingUtilization"
                    :utilization-data="utilizationData"
                    :selected-device-ids="effectiveSelectedIds"
                    :devices="devices"
                    :selected-period="selectedPeriod"
                />
                
                <!-- Vehicle Type Comparison -->
                <VehicleTypeComparison
                    :loading="loadingUtilization"
                    :utilization-data="utilizationData"
                    :devices="devices"
                />
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