<template>
    <Card>
        <CardHeader>
            <CardTitle class="text-base">เปรียบเทียบตามประเภท</CardTitle>
            <CardDescription class="text-xs">อัตราการใช้งานแยกตามประเภท</CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="h-8 w-8 animate-spin text-gray-400" />
                <span class="ml-2 text-gray-600">กำลังโหลดข้อมูล...</span>
            </div>
            
            <div v-else-if="typeComparisonData.length > 0" class="space-y-4">
                <!-- Simple bar chart (matching StopAnalysisTab style) -->
                <div class="w-full h-full flex flex-col justify-center">
                    <div class="relative flex items-end justify-center h-60 gap-3 px-4">
                        <!-- Reference lines with gray colors -->
                        <!-- 20% reference line -->
                        <div class="absolute left-4 right-4 h-px opacity-40" style="bottom: 64px; background-color: #9ca3af;"></div>
                        <div class="absolute right-2 text-xs" style="bottom: 64px; color: #9ca3af;">20%</div>
                        <!-- 40% reference line -->
                        <div class="absolute left-4 right-4 h-px opacity-40" style="bottom: 104px; background-color: #9ca3af;"></div>
                        <div class="absolute right-2 text-xs" style="bottom: 104px; color: #9ca3af;">40%</div>
                        <!-- 60% reference line -->
                        <div class="absolute left-4 right-4 h-px opacity-40" style="bottom: 144px; background-color: #9ca3af;"></div>
                        <div class="absolute right-2 text-xs" style="bottom: 144px; color: #9ca3af;">60%</div>
                        <!-- 80% reference line -->
                        <div class="absolute left-4 right-4 h-px opacity-40" style="bottom: 184px; background-color: #9ca3af;"></div>
                        <div class="absolute right-2 text-xs" style="bottom: 184px; color: #9ca3af;">80%</div>
                        <!-- 100% reference line -->
                        <div class="absolute left-4 right-4 h-px opacity-50" style="bottom: 224px; background-color: #9ca3af;"></div>
                        <div class="absolute right-2 text-xs" style="bottom: 224px; color: #9ca3af;">100%</div>
                        <div 
                            v-for="(type, index) in typeComparisonData" 
                            :key="index"
                            class="flex flex-col items-center flex-1"
                        >
                            <div class="relative w-full flex flex-col items-center">
                                <!-- Bar -->
                                <div 
                                    class="w-full rounded-t transition-all duration-300 cursor-pointer"
                                    :class="`hover:brightness-110`"
                                    :style="{ 
                                        height: type.avgUtilization > 0 ? Math.max((type.avgUtilization / 100 * 200), 12) + 'px' : '6px',
                                        backgroundColor: getTypeColor(type.avgUtilization)
                                    }"
                                    @mouseenter="showSimpleTooltip($event, type)"
                                    @mouseleave="hideTooltip"
                                >
                                    <!-- Count label on top of bar -->
                                    <div class="text-xs text-white font-semibold text-center py-1">
                                        {{ type.avgUtilization > 0 ? type.avgUtilization + '%' : '' }}
                                    </div>
                                </div>
                                <!-- Label below bar -->
                                <div class="text-xs mt-2 text-gray-600 text-center">{{ type.name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 text-center mt-4">
                        อัตราการใช้งานเฉลี่ยแยกตามประเภท
                    </div>
                    
                    <!-- Tooltip -->
                    <div v-if="tooltip.show" 
                        :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
                        class="fixed z-10 bg-gray-900 text-white text-xs rounded px-2 py-1 pointer-events-none whitespace-nowrap transform -translate-x-1/2"
                    >
                        <div class="font-semibold">{{ tooltip.typeName }}</div>
                        <div>อัตราการใช้งาน: {{ tooltip.avgUtilization }}%</div>
                        <div>จำนวนรถ: {{ tooltip.vehicleCount }} คัน</div>
                        <div>สถานะ: {{ tooltip.status }}</div>
                    </div>
                </div>
                
                <!-- Type Analysis Cards (matching UtilizationChart style) -->
                <div v-if="showLegend !== false" class="space-y-4 pt-4 border-t">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                        <div v-for="type in typeComparisonData" 
                            :key="type.name"
                            class="border rounded-lg transition-all bg-white hover:shadow-sm"
                        >
                            <!-- Type Header -->
                            <div class="p-3 border-b bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div 
                                            class="w-3 h-3 rounded"
                                            :style="{ backgroundColor: type.vehicleColor }"
                                        ></div>
                                        <span class="text-sm font-medium">{{ type.name }}</span>
                                    </div>
                                    <span class="text-lg font-bold" :style="{ color: type.vehicleColor }">
                                        {{ type.avgUtilization }}%
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ type.vehicleCount }} คัน • สูงสุด {{ type.maxUtilization }}% • ต่ำสุด {{ type.minUtilization }}%
                                </div>
                            </div>
                            
                            <!-- Individual Vehicles -->
                            <div class="p-2">
                                <div class="flex flex-wrap gap-1">
                                    <div v-for="vehicle in type.vehicles" 
                                         :key="vehicle.id"
                                         class="flex items-center gap-1 text-xs px-2 py-1 rounded border bg-white border-gray-200"
                                         :title="vehicle.name"
                                    >
                                        <div 
                                            class="w-2 h-2 rounded"
                                            :style="{ backgroundColor: props.getVehicleColor ? props.getVehicleColor(vehicle.id) : type.vehicleColor }"
                                        ></div>
                                        <span>{{ vehicle.shortname || vehicle.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
                ไม่มีข้อมูลสำหรับการเปรียบเทียบ
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Loader2 } from 'lucide-vue-next';

interface Vehicle {
    id: number;
    name: string;
    type: string;
    shortname?: string;
}

interface Props {
    loading: boolean;
    utilizationData: Record<string, Record<number, number>>;
    devices: Vehicle[];
    getVehicleColor?: (deviceId: number) => string;
    showLegend?: boolean;
}

const props = defineProps<Props>();

// Tooltip state
const tooltip = ref({
    show: false,
    x: 0,
    y: 0,
    typeName: '',
    avgUtilization: 0,
    vehicleCount: 0,
    status: ''
});

// Process data by vehicle type
const typeComparisonData = computed(() => {
    const typeGroups = new Map<string, number[]>();
    
    // Group utilization values by vehicle type
    Object.values(props.utilizationData).forEach(dayData => {
        props.devices.forEach(vehicle => {
            const utilization = dayData[vehicle.id] || 0;
            if (utilization > 0) {
                if (!typeGroups.has(vehicle.type)) {
                    typeGroups.set(vehicle.type, []);
                }
                typeGroups.get(vehicle.type)?.push(utilization);
            }
        });
    });
    
    // Calculate statistics for each type
    return Array.from(typeGroups.entries()).map(([typeName, values]) => {
        const avgUtilization = values.length > 0 
            ? Math.round(values.reduce((a, b) => a + b, 0) / values.length)
            : 0;
        
        const maxUtilization = Math.max(...values, 0);
        const minUtilization = values.length > 0 ? Math.min(...values) : 0;
        const vehicleCount = props.devices.filter(d => d.type === typeName).length;
        
        // Get representative vehicle color for this type
        const typeVehicles = props.devices.filter(d => d.type === typeName);
        const vehicleColor = typeVehicles.length > 0 && props.getVehicleColor 
            ? props.getVehicleColor(typeVehicles[0].id) 
            : getTypeColor(avgUtilization);
        
        return {
            name: typeName,
            avgUtilization,
            maxUtilization,
            minUtilization,
            vehicleCount,
            vehicleColor,
            vehicles: typeVehicles
        };
    }).sort((a, b) => b.avgUtilization - a.avgUtilization); // Sort by utilization desc
});

// Get color based on utilization level
const getTypeColor = (utilization: number) => {
    if (utilization >= 60) return '#22c55e'; // Green - Excellent
    if (utilization >= 40) return '#3b82f6'; // Blue - Good
    if (utilization >= 25) return '#f59e0b'; // Yellow - Fair
    if (utilization >= 15) return '#f97316'; // Orange - Poor
    return '#ef4444'; // Red - Very Poor
};

// Get status text based on utilization
const getTypeStatus = (utilization: number) => {
    if (utilization >= 60) return 'ดีเยี่ยม';
    if (utilization >= 40) return 'ดี';
    if (utilization >= 25) return 'ปานกลาง';
    if (utilization >= 15) return 'ต่ำ';
    return 'ต่ำมาก';
};

// Get card styling based on utilization
const getTypeCardClass = (utilization: number) => {
    if (utilization >= 40) return 'bg-green-50 border-green-200';
    if (utilization >= 25) return 'bg-yellow-50 border-yellow-200';
    if (utilization >= 15) return 'bg-orange-50 border-orange-200';
    return 'bg-red-50 border-red-200';
};


// Calculate max utilization for scaling bars (fixed to 100% scale)
const maxUtilization = computed(() => {
    return 100; // Always scale to 100% for consistent visualization
});

// Updated tooltip handlers to match StopAnalysisTab style
const showSimpleTooltip = (event: MouseEvent, type: any) => {
    tooltip.value = {
        show: true,
        x: event.clientX,
        y: event.clientY - 40,
        typeName: type.name,
        avgUtilization: type.avgUtilization,
        vehicleCount: type.vehicleCount,
        status: getTypeStatus(type.avgUtilization)
    };
};

const hideTooltip = () => {
    tooltip.value.show = false;
};
</script>

