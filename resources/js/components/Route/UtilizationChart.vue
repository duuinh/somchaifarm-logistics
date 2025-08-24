<template>
    <Card>
        <CardHeader>
            <div>
                <CardTitle class="text-base">อัตราการใช้งานรถ</CardTitle>
                <CardDescription class="text-xs">อัตราการวิ่งงานต่อวัน</CardDescription>
            </div>
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="h-8 w-8 animate-spin text-gray-400" />
                <span class="ml-2 text-gray-600">กำลังโหลดข้อมูล...</span>
            </div>
            
            <div v-else-if="chartData.length > 0" class="space-y-4">
                <!-- Simple SVG Line Chart (matching height) -->
                <div class="relative h-60 bg-white">
                    <svg class="w-full h-full" viewBox="0 0 800 240" preserveAspectRatio="xMidYMid meet">
                        <!-- Grid lines -->
                        <g class="grid">
                            <!-- Horizontal grid lines -->
                            <line v-for="i in 5" :key="`h-grid-${i}`"
                                :x1="60" :y1="40 + (i * 32)"
                                :x2="740" :y2="40 + (i * 32)"
                                stroke="#e5e7eb" stroke-width="1"
                            />
                            <!-- Y-axis labels -->
                            <text v-for="i in 6" :key="`y-label-${i}`"
                                :x="50" :y="205 - (i * 32)"
                                text-anchor="end"
                                class="text-xs fill-gray-600 font-medium"
                                font-family="Inter, system-ui, sans-serif"
                            >
                                {{ (i * 20) }}%
                            </text>
                        </g>
                        
                        <!-- Data lines -->
                        <g v-for="vehicle in visibleVehicles" :key="vehicle.id">
                            <polyline
                                :points="getSimpleLinePoints(vehicle.id)"
                                fill="none"
                                :stroke="getVehicleColor(vehicle.id)"
                                stroke-width="2"
                                :stroke-dasharray="hiddenVehicles.includes(vehicle.id) ? '5,5' : '0'"
                                :opacity="hiddenVehicles.includes(vehicle.id) ? 0.3 : 1"
                            />
                            <!-- Data points -->
                            <circle v-for="(point, pIndex) in getSimpleDataPoints(vehicle.id)" 
                                :key="`point-${vehicle.id}-${pIndex}`"
                                :cx="point.x"
                                :cy="point.y"
                                r="4"
                                :fill="getVehicleColor(vehicle.id)"
                                :opacity="hiddenVehicles.includes(vehicle.id) ? 0.3 : 1"
                                class="cursor-pointer hover:r-6"
                                @mouseenter="showSimpleTooltip($event, point, vehicle)"
                                @mouseleave="hideTooltip"
                            />
                        </g>
                        
                        <!-- X-axis labels -->
                        <g>
                            <text v-for="(label, index) in xAxisLabels" 
                                :key="`x-label-${index}`"
                                :x="60 + (index * ((680) / (xAxisLabels.length - 1)))"
                                y="230"
                                text-anchor="middle"
                                class="text-xs fill-gray-600 font-medium"
                                font-family="Inter, system-ui, sans-serif"
                            >
                                {{ label }}
                            </text>
                        </g>
                    </svg>
                    
                    <!-- Tooltip -->
                    <div v-if="tooltip.show" 
                        :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
                        class="fixed z-10 bg-gray-900 text-white text-xs rounded px-2 py-1 pointer-events-none whitespace-nowrap transform -translate-x-1/2"
                    >
                        {{ tooltip.content }}
                    </div>
                </div>
                
                <!-- Vehicle Type Filters and Analysis -->
                <div v-if="showLegend !== false" class="space-y-4 pt-4 border-t">
                    <!-- Type Filter Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <span class="text-xs font-medium text-gray-600 self-center">กรองตามประเภท:</span>
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
                                :style="{ backgroundColor: hiddenVehicleTypes.includes(typeGroup.type) ? '#9ca3af' : typeGroup.avgUtilization < 15 ? '#ef4444' : typeGroup.avgUtilization < 30 ? '#f97316' : '#22c55e' }"
                            ></div>
                            <span>{{ typeGroup.type }} ({{ typeGroup.avgUtilization }}%)</span>
                        </button>
                    </div>
                    
                    <!-- Detailed Type Analysis -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                        <div v-for="typeGroup in vehicleTypeGroups" 
                            :key="typeGroup.type"
                            class="border rounded-lg transition-all"
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
                                            :style="{ backgroundColor: hiddenVehicleTypes.includes(typeGroup.type) ? '#9ca3af' : typeGroup.avgUtilization < 15 ? '#ef4444' : typeGroup.avgUtilization < 30 ? '#f97316' : '#22c55e' }"
                                        ></div>
                                        <span class="text-sm font-medium">{{ typeGroup.type }}</span>
                                    </button>
                                    <span class="text-lg font-bold" :style="{ color: hiddenVehicleTypes.includes(typeGroup.type) ? '#9ca3af' : typeGroup.avgUtilization < 15 ? '#ef4444' : typeGroup.avgUtilization < 30 ? '#f97316' : '#22c55e' }">
                                        {{ typeGroup.avgUtilization }}%
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ typeGroup.vehicles.length }} คัน • เฉลี่ย {{ selectedPeriod }} วัน
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
                                        <span>{{ getVehicleTagName(vehicle) }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Summary -->
                <div class="grid grid-cols-3 gap-4 pt-4 border-t text-xs">
                    <div>
                        <div class="text-gray-500">ค่าเฉลี่ยรวม</div>
                        <div class="font-semibold">{{ averageUtilization }}%</div>
                    </div>
                    <div>
                        <div class="text-gray-500">สูงสุด</div>
                        <div class="font-semibold">{{ maxUtilization }}%</div>
                    </div>
                    <div>
                        <div class="text-gray-500">ต่ำสุด</div>
                        <div class="font-semibold">{{ minUtilization }}%</div>
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
    selectedDeviceIds: number[];
    devices: Vehicle[];
    selectedPeriod?: number;
    getVehicleColor?: (deviceId: number) => string;
    showLegend?: boolean;
    hiddenVehicles?: number[];
    hiddenVehicleTypes?: string[];
}

const props = defineProps<Props>();

// Use external filter state if provided, otherwise use local state
const hiddenVehicles = computed(() => props.hiddenVehicles || []);
const hiddenVehicleTypes = computed(() => props.hiddenVehicleTypes || []);
const tooltip = ref({
    show: false,
    x: 0,
    y: 0,
    content: ''
});

// Get selected period from props or default to 7
const selectedPeriod = computed(() => props.selectedPeriod || 7);

// Get selected vehicles
const selectedVehicles = computed(() => {
    return props.devices.filter(d => props.selectedDeviceIds.includes(d.id));
});

// Visible vehicles (not hidden by individual or type filter)
const visibleVehicles = computed(() => {
    return selectedVehicles.value.filter(v => 
        !hiddenVehicles.value.includes(v.id) && 
        !hiddenVehicleTypes.value.includes(v.type)
    );
});

// Group vehicles by type with average utilization
const vehicleTypeGroups = computed(() => {
    const groups = new Map<string, Vehicle[]>();
    
    // Group vehicles by type
    selectedVehicles.value.forEach(vehicle => {
        if (!groups.has(vehicle.type)) {
            groups.set(vehicle.type, []);
        }
        groups.get(vehicle.type)?.push(vehicle);
    });
    
    // Calculate average utilization for each type
    return Array.from(groups.entries()).map(([type, vehicles]) => {
        const allValues = chartData.value.flatMap(d => 
            vehicles.map(v => d.values[v.id] || 0)
        ).filter(v => v > 0);
        
        const avgUtilization = allValues.length > 0 
            ? Math.round(allValues.reduce((a, b) => a + b, 0) / allValues.length)
            : 0;
        
        return {
            type,
            vehicles,
            avgUtilization
        };
    }).sort((a, b) => b.avgUtilization - a.avgUtilization); // Sort by utilization desc
});

// Process chart data
const chartData = computed(() => {
    const dates = Object.keys(props.utilizationData).sort();
    const recentDates = selectedPeriod.value === 30 ? dates.slice(-30) : dates.slice(-selectedPeriod.value);
    
    return recentDates.map(date => ({
        date,
        values: props.utilizationData[date] || {}
    }));
});

// X-axis labels
const xAxisLabels = computed(() => {
    const dates = chartData.value.map(d => d.date);
    const interval = selectedPeriod.value === 30 ? 5 : 1;
    
    return dates.filter((_, index) => {
        if (selectedPeriod.value === 7) return true;
        return index % interval === 0 || index === dates.length - 1;
    }).map(date => {
        const d = new Date(date + 'T00:00:00');
        return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
    });
});

// Calculate line points for SVG
const getLinePoints = (vehicleId: number) => {
    const points = chartData.value.map((data, index) => {
        const x = 60 + (index * (720 / (chartData.value.length - 1 || 1)));
        const value = Math.min(Math.max(data.values[vehicleId] || 0, 0), 100); // Clamp between 0-100%
        const y = 340 - (value * 3); // Scale to 40-340 pixels for 0-100%
        return `${x},${y}`;
    });
    return points.join(' ');
};

// Get data points for circles
const getDataPoints = (vehicleId: number) => {
    return chartData.value.map((data, index) => {
        const x = 60 + (index * (720 / (chartData.value.length - 1 || 1)));
        const rawValue = data.values[vehicleId] || 0;
        const value = Math.min(Math.max(rawValue, 0), 100); // Clamp between 0-100%
        const y = 340 - (value * 3); // Scale to 40-340 pixels for 0-100%
        return {
            x,
            y,
            value,
            date: data.date,
            vehicleId
        };
    });
};

// Get vehicle color
const getVehicleColor = (deviceId: number) => {
    if (props.getVehicleColor) {
        return props.getVehicleColor(deviceId);
    }
    const device = props.devices.find(d => d.id === deviceId);
    return device?.color || '#0000FF'; // Default blue color
};

// Get vehicle display name for tooltips - use full cleaned name for better readability
const getVehicleDisplayName = (vehicle: Vehicle) => {
    // Use cleaned full name for better context in tooltips
    return vehicle.name;
};

// Get vehicle tag name - use shortname for compact legend tags
const getVehicleTagName = (vehicle: Vehicle) => {
    return vehicle.shortname || vehicle.name;
};

// Toggle vehicle visibility
const toggleVehicle = (vehicleId: number) => {
    const index = hiddenVehicles.value.indexOf(vehicleId);
    if (index > -1) {
        hiddenVehicles.value.splice(index, 1);
    } else {
        hiddenVehicles.value.push(vehicleId);
    }
};

// Toggle vehicle type visibility
const toggleVehicleType = (vehicleType: string) => {
    const index = hiddenVehicleTypes.value.indexOf(vehicleType);
    if (index > -1) {
        hiddenVehicleTypes.value.splice(index, 1);
    } else {
        hiddenVehicleTypes.value.push(vehicleType);
    }
};

// Tooltip handlers
const showTooltip = (event: MouseEvent, point: any) => {
    const vehicle = selectedVehicles.value.find(v => v.id === point.vehicleId);
    const date = new Date(point.date + 'T00:00:00');
    
    tooltip.value = {
        show: true,
        x: point.x,
        y: point.y - 30,
        content: `${getVehicleDisplayName(vehicle || { id: 0, name: '', type: '' })}: ${point.value}% (${date.toLocaleDateString('th-TH')})`
    };
};

const hideTooltip = () => {
    tooltip.value.show = false;
};

// New tooltip handler for HTML bars
const showBarTooltip = (event: MouseEvent, vehicle: Vehicle, data: any) => {
    const utilization = data.values[vehicle.id] || 0;
    const date = new Date(data.date + 'T00:00:00');
    
    tooltip.value = {
        show: true,
        x: event.clientX,
        y: event.clientY - 40,
        content: `${getVehicleDisplayName(vehicle)}: ${utilization}% (${date.toLocaleDateString('th-TH')})`
    };
};

// Format date labels for HTML chart
const formatDateLabel = (date: string) => {
    const d = new Date(date + 'T00:00:00');
    return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
};

// HTML chart functions
const getHtmlLinePoints = (vehicleId: number) => {
    const points = chartData.value.map((data, index) => {
        const x = (index * (100 / (chartData.value.length - 1 || 1)));
        const value = Math.min(Math.max(data.values[vehicleId] || 0, 0), 100);
        const y = 100 - value;
        return `${x},${y}`;
    });
    return points.join(' ');
};

const getHtmlDataPoints = (vehicleId: number) => {
    return chartData.value.map((data, index) => {
        const x = (index * (100 / (chartData.value.length - 1 || 1)));
        const rawValue = data.values[vehicleId] || 0;
        const value = Math.min(Math.max(rawValue, 0), 100);
        const y = 100 - value;
        return {
            x: x,
            y: y,
            value,
            date: data.date,
            vehicleId
        };
    });
};

const showHtmlTooltip = (event: MouseEvent, point: any, vehicle: Vehicle) => {
    const date = new Date(point.date + 'T00:00:00');
    
    tooltip.value = {
        show: true,
        x: event.clientX,
        y: event.clientY - 40,
        content: `${getVehicleDisplayName(vehicle)}: ${point.value}% (${date.toLocaleDateString('th-TH')})`
    };
};

// Shadcn-vue chart data transformation
const chartDataForShadcn = computed(() => {
    return chartData.value.map(data => {
        const result: any = {
            date: formatDateLabel(data.date)
        };
        
        // Add each vehicle's data as a separate field
        visibleVehicles.value.forEach(vehicle => {
            const vehicleName = getVehicleTagName(vehicle);
            result[vehicleName] = data.values[vehicle.id] || 0;
        });
        
        return result;
    });
});

// Vehicle categories for chart
const vehicleCategories = computed(() => {
    return visibleVehicles.value.map(vehicle => getVehicleTagName(vehicle));
});

// Vehicle colors for chart
const vehicleColors = computed(() => {
    return visibleVehicles.value.map(vehicle => getVehicleColor(vehicle.id));
});

// Chart configuration for shadcn-vue
const chartConfig = computed(() => {
    const config: any = {};
    
    visibleVehicles.value.forEach(vehicle => {
        const vehicleName = getVehicleTagName(vehicle);
        config[vehicleName] = {
            label: getVehicleDisplayName(vehicle),
            color: getVehicleColor(vehicle.id)
        };
    });
    
    return config;
});

// Simple line chart functions for h-60 (240px viewBox)
const getSimpleLinePoints = (vehicleId: number) => {
    const points = chartData.value.map((data, index) => {
        const x = 60 + (index * (680 / (chartData.value.length - 1 || 1)));
        const value = Math.min(Math.max(data.values[vehicleId] || 0, 0), 100);
        const y = 200 - (value * 1.6); // Scale to 40-200 pixels for 0-100%
        return `${x},${y}`;
    });
    return points.join(' ');
};

const getSimpleDataPoints = (vehicleId: number) => {
    return chartData.value.map((data, index) => {
        const x = 60 + (index * (680 / (chartData.value.length - 1 || 1)));
        const rawValue = data.values[vehicleId] || 0;
        const value = Math.min(Math.max(rawValue, 0), 100);
        const y = 200 - (value * 1.6); // Scale to 40-200 pixels for 0-100%
        return {
            x,
            y,
            value,
            date: data.date,
            vehicleId
        };
    });
};

const showSimpleTooltip = (event: MouseEvent, point: any, vehicle: Vehicle) => {
    const date = new Date(point.date + 'T00:00:00');
    
    tooltip.value = {
        show: true,
        x: event.clientX,
        y: event.clientY - 40,
        content: `${getVehicleDisplayName(vehicle)}: ${point.value}% (${date.toLocaleDateString('th-TH')})`
    };
};

// Statistics
const averageUtilization = computed(() => {
    const allValues = chartData.value.flatMap(d => 
        visibleVehicles.value.map(v => d.values[v.id] || 0)
    ).filter(v => v > 0);
    
    if (allValues.length === 0) return 0;
    return Math.round(allValues.reduce((a, b) => a + b, 0) / allValues.length);
});

const maxUtilization = computed(() => {
    const allValues = chartData.value.flatMap(d => 
        visibleVehicles.value.map(v => d.values[v.id] || 0)
    );
    return Math.max(...allValues, 0);
});

const minUtilization = computed(() => {
    const allValues = chartData.value.flatMap(d => 
        visibleVehicles.value.map(v => d.values[v.id] || 0)
    ).filter(v => v > 0);
    
    if (allValues.length === 0) return 0;
    return Math.min(...allValues);
});

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