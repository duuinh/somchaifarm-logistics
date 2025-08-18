<template>
    <Card>
        <CardHeader>
            <CardTitle class="text-base">การเปรียบเทียบประเภทรถ</CardTitle>
            <CardDescription class="text-xs">เปรียบเทียบประสิทธิภาพแต่ละประเภท</CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="h-8 w-8 animate-spin text-gray-400" />
                <span class="ml-2 text-gray-600">กำลังโหลดข้อมูล...</span>
            </div>
            
            <div v-else-if="typeComparisonData.length > 0" class="space-y-4">
                <!-- Bar Chart Comparison -->
                <div class="relative" style="height: 300px;">
                    <svg class="w-full h-full" viewBox="0 0 800 300" preserveAspectRatio="none">
                        <!-- Grid lines -->
                        <g class="grid">
                            <!-- Horizontal grid lines -->
                            <line v-for="i in 5" :key="`h-grid-${i}`"
                                :x1="80" :y1="30 + (i * 50)"
                                :x2="750" :y2="30 + (i * 50)"
                                stroke="#e5e7eb" stroke-width="1"
                            />
                            <!-- Y-axis labels -->
                            <text v-for="i in 6" :key="`y-label-${i}`"
                                :x="70" :y="285 - (i * 50)"
                                text-anchor="end"
                                class="text-xs fill-gray-600 font-medium"
                                font-family="Inter, system-ui, sans-serif"
                            >
                                {{ (i * 20) }}%
                            </text>
                        </g>
                        
                        <!-- Bars -->
                        <g>
                            <rect v-for="(type, index) in typeComparisonData" 
                                :key="type.name"
                                :x="getBarX(index)"
                                :y="280 - (type.avgUtilization * 2.5)"
                                :width="getBarWidth()"
                                :height="type.avgUtilization * 2.5"
                                :fill="getTypeColor(type.avgUtilization)"
                                :opacity="0.8"
                                class="cursor-pointer"
                                @mouseenter="showTypeTooltip($event, type, index)"
                                @mouseleave="hideTooltip"
                            />
                            
                            <!-- Bar value labels -->
                            <text v-for="(type, index) in typeComparisonData"
                                :key="`value-${type.name}`"
                                :x="getBarCenterX(index)"
                                :y="275 - (type.avgUtilization * 2.5)"
                                text-anchor="middle"
                                class="text-xs font-semibold fill-white"
                                font-family="Inter, system-ui, sans-serif"
                            >
                                {{ type.avgUtilization }}%
                            </text>
                        </g>
                        
                        <!-- X-axis labels -->
                        <g>
                            <text v-for="(type, index) in typeComparisonData"
                                :key="`x-label-${type.name}`"
                                :x="getBarCenterX(index)"
                                y="295"
                                text-anchor="middle"
                                class="text-xs fill-gray-700 font-medium"
                                font-family="Inter, system-ui, sans-serif"
                            >
                                {{ type.name }}
                            </text>
                        </g>
                    </svg>
                    
                    <!-- Tooltip -->
                    <div v-if="tooltip.show" 
                        :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
                        class="absolute z-10 bg-gray-900 text-white text-xs rounded px-3 py-2 pointer-events-none whitespace-nowrap"
                    >
                        <div class="font-semibold">{{ tooltip.typeName }}</div>
                        <div>เฉลี่ย: {{ tooltip.avgUtilization }}%</div>
                        <div>จำนวน: {{ tooltip.vehicleCount }} คัน</div>
                        <div class="text-xs opacity-75">{{ tooltip.status }}</div>
                    </div>
                </div>
                
                <!-- Type Statistics Table -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 pt-4">
                    <div v-for="type in typeComparisonData" 
                        :key="type.name"
                        class="p-4 border rounded-lg"
                        :class="getTypeCardClass(type.avgUtilization)"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="font-medium">{{ type.name }}</div>
                            <div class="text-2xl font-bold" :style="{ color: getTypeColor(type.avgUtilization) }">
                                {{ type.avgUtilization }}%
                            </div>
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>จำนวนรถ:</span>
                                <span class="font-medium">{{ type.vehicleCount }} คัน</span>
                            </div>
                            <div class="flex justify-between">
                                <span>สูงสุด:</span>
                                <span class="font-medium">{{ type.maxUtilization }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>ต่ำสุด:</span>
                                <span class="font-medium">{{ type.minUtilization }}%</span>
                            </div>
                            <div class="pt-2 border-t">
                                <div class="text-xs" :style="{ color: getTypeColor(type.avgUtilization) }">
                                    {{ getTypeStatus(type.avgUtilization) }}
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
        
        return {
            name: typeName,
            avgUtilization,
            maxUtilization,
            minUtilization,
            vehicleCount
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

// Calculate bar dimensions based on number of types
const getBarWidth = () => {
    const chartWidth = 670; // Available width (750 - 80)
    const totalTypes = typeComparisonData.value.length || 4;
    const spacing = 20; // Space between bars
    return Math.max(60, (chartWidth - (spacing * (totalTypes + 1))) / totalTypes);
};

const getBarX = (index: number) => {
    const barWidth = getBarWidth();
    const spacing = 20;
    return 80 + spacing + (index * (barWidth + spacing));
};

const getBarCenterX = (index: number) => {
    return getBarX(index) + (getBarWidth() / 2);
};

// Tooltip handlers
const showTypeTooltip = (event: MouseEvent, type: any, index: number) => {
    tooltip.value = {
        show: true,
        x: getBarCenterX(index),
        y: 280 - (type.avgUtilization * 2.5) - 10,
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

<style scoped>
svg {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

svg text {
    font-weight: 500;
    letter-spacing: 0.025em;
}
</style>