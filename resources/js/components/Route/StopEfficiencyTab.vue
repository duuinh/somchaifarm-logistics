<template>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">การวิเคราะห์จุดหยุด</h2>
                <p class="text-sm text-gray-600">วิเคราะห์รูปแบบและระยะเวลาการหยุดของยานพาหนะ</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ displayDateRange }} | {{ filteredVehicles.length }} คัน
            </div>
        </div>

        <!-- Controls Section -->
        <div class="space-y-4">
            <!-- Time Period Controls -->
            <div class="flex flex-wrap gap-4 items-center p-4 bg-gray-50 rounded-lg">
                <span class="text-sm font-medium text-gray-700">ช่วงเวลา:</span>
            
            <!-- Quick Period Buttons -->
            <div class="flex gap-1">
                <button 
                    @click="selectYesterday()"
                    :class="[
                        isYesterday && !customDateRange ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border',
                        'px-3 py-1 text-xs rounded hover:bg-blue-50'
                    ]"
                >
                    เมื่อวาน
                </button>
                <button 
                    @click="selectQuickPeriod(1)"
                    :class="[
                        selectedPeriod === 1 && !customDateRange && !isYesterday ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border',
                        'px-3 py-1 text-xs rounded hover:bg-blue-50'
                    ]"
                >
                    วันนี้
                </button>
                <button 
                    @click="selectQuickPeriod(7)"
                    :class="[
                        selectedPeriod === 7 && !customDateRange ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border',
                        'px-3 py-1 text-xs rounded hover:bg-blue-50'
                    ]"
                >
                    7 วัน
                </button>
                <button 
                    @click="selectQuickPeriod(30)"
                    :class="[
                        selectedPeriod === 30 && !customDateRange ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border',
                        'px-3 py-1 text-xs rounded hover:bg-blue-50'
                    ]"
                >
                    30 วัน
                </button>
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
                <button 
                    @click="applyCustomDateRange"
                    :class="[
                        customDateRange ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border',
                        'px-2 py-1 text-xs rounded hover:bg-blue-50'
                    ]"
                >
                    ใช้
                </button>
            </div>
        </div>
        
        <!-- Radius Filter Control -->
        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">รัศมีการหยุด:</span>
            <div class="flex items-center gap-2">
                <input
                    type="range"
                    v-model="stopRadius"
                    min="200"
                    max="1000"
                    step="50"
                    class="w-32"
                />
                <input
                    type="number"
                    v-model="stopRadius"
                    min="200"
                    max="1000"
                    step="50"
                    class="w-20 px-2 py-1 text-sm border border-gray-300 rounded"
                />
                <span class="text-sm text-gray-600">เมตร</span>
            </div>
            <div class="text-xs text-gray-500">
                (ระยะทางขั้นต่ำในการตรวจจับจุดหยุด: 200ม. - 1กม.)
            </div>
        </div>
    </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">จำนวนจุดหยุดทั้งหมด</div>
                <div class="text-2xl font-bold text-blue-600">{{ totalStops }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">เวลาหยุดเฉลี่ย</div>
                <div class="text-2xl font-bold text-green-600">{{ averageStopDuration }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">จุดหยุดผิดปกติ</div>
                <div class="text-2xl font-bold text-orange-600">{{ inefficientStops }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">หยุดนานสุด</div>
                <div class="text-2xl font-bold text-gray-700">{{ longestStopDuration }}</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Stop Duration Distribution -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-medium mb-4">การกระจายของระยะเวลาหยุด (15 นาทีขึ้นไป)</h3>
                <div class="h-64 flex items-center justify-center text-gray-400">
                    <!-- Chart placeholder -->
                    <div v-if="stopDurationData.length === 0" class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <div class="mt-2">ไม่มีข้อมูลการหยุด</div>
                    </div>
                    <!-- Simple bar chart -->
                    <div v-else class="w-full h-full flex flex-col justify-center">
                        <div class="flex items-end justify-center h-40 gap-3 px-4">
                            <div 
                                v-for="(bucket, index) in stopDurationBuckets" 
                                :key="index"
                                class="flex flex-col items-center flex-1"
                            >
                                <div class="relative w-full flex flex-col items-center">
                                    <!-- Bar -->
                                    <div 
                                        class="w-full bg-blue-500 rounded-t transition-all duration-300 hover:bg-blue-600"
                                        :style="{ 
                                            height: bucket.count > 0 ? Math.max((bucket.count / maxBucketCount * 120), 4) + 'px' : '2px',
                                            backgroundColor: getBucketColor(bucket.min)
                                        }"
                                    >
                                        <!-- Count label on top of bar -->
                                        <div class="text-xs text-white font-semibold text-center py-1">
                                            {{ bucket.count > 0 ? bucket.count : '' }}
                                        </div>
                                    </div>
                                    <!-- Label below bar -->
                                    <div class="text-xs mt-2 text-gray-600 text-center">{{ bucket.label }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 text-center mt-4">
                            จำนวนจุดหยุดแยกตามระยะเวลา
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stop Statistics by Vehicle -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-medium mb-4">สถิติการหยุดแยกตามคัน</h3>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <div 
                        v-for="vehicle in vehicleStatistics" 
                        :key="vehicle.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded"
                    >
                        <div class="flex items-center gap-3">
                            <div 
                                class="w-4 h-4 rounded-full"
                                :style="{ backgroundColor: vehicle.color }"
                            ></div>
                            <div>
                                <div class="font-medium">{{ vehicle.name }}</div>
                                <div class="text-sm text-gray-600">{{ vehicle.totalStops }} จุดหยุด</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-700">
                                {{ vehicle.totalDuration }}
                            </div>
                            <div class="text-sm text-gray-600">เฉลี่ย {{ vehicle.avgDuration }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Stop Analysis -->
        <div class="bg-white p-6 rounded-lg border shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">รายละเอียดจุดหยุดนาน (>60 นาที)</h3>
                <div class="text-sm text-gray-600">
                    ทั้งหมด {{ inefficientStopsCount }} รายการ
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ยานพาหนะ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานที่</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เวลาเริ่ม</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ระยะเวลา</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ประเภทจุด</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="stop in inefficientStopDetails" :key="`${stop.deviceId}-${stop.startTime}`">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div 
                                        class="w-3 h-3 rounded-full mr-2"
                                        :style="{ backgroundColor: stop.vehicleColor }"
                                    ></div>
                                    <div class="text-sm font-medium text-gray-900">{{ stop.vehicleName }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button 
                                    @click="$emit('show-on-map', stop)"
                                    class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer flex items-center gap-1"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ stop.location }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatStartTimeWithDayOfWeek(stop.startTime) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getStopDurationClass(stop.duration)"
                                >
                                    {{ formatDuration(stop.duration) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getLocationTypeClass(stop)"
                                >
                                    {{ getStopLocationType(stop) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Controls -->
            <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-700">
                        แสดง {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, inefficientStopsCount) }} จาก {{ inefficientStopsCount }} รายการ
                    </span>
                </div>
                
                <div class="flex items-center gap-1">
                    <!-- Previous button -->
                    <button
                        @click="currentPage--"
                        :disabled="currentPage === 1"
                        class="px-3 py-1 text-sm rounded border hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <!-- Page numbers -->
                    <template v-for="page in totalPages" :key="page">
                        <button
                            v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                            @click="currentPage = page"
                            :class="[
                                'px-3 py-1 text-sm rounded border',
                                currentPage === page ? 'bg-blue-600 text-white' : 'hover:bg-gray-50'
                            ]"
                        >
                            {{ page }}
                        </button>
                        <span 
                            v-else-if="page === currentPage - 2 || page === currentPage + 2"
                            class="px-2 text-gray-500"
                        >
                            ...
                        </span>
                    </template>
                    
                    <!-- Next button -->
                    <button
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1 text-sm rounded border hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { officeCoordinates, calculateDistance, pickupLocations, deliveryLocations } from '@/composables/useRouteFiltering';

interface Props {
    selectedDeviceIds: number[];
    selectedDate: string;
    devices: Array<{ id: number; name: string; type: string }>;
    vehicleColors: string[];
    routeHistory: any[];
    getVehicleColor: (selectedIds: number[], deviceId: number) => string;
    routeAnalysisRadius?: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'period-change': [days: number];
    'date-range-change': [startDate: string, endDate: string];
    'radius-change': [radius: number];
    'show-on-map': [stop: any];
}>();

// Stop radius state
const stopRadius = ref(props.routeAnalysisRadius || 200);

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalPages = computed(() => Math.ceil(inefficientStopsCount.value / itemsPerPage.value));

// Watch for radius changes
watch(stopRadius, (newRadius) => {
    emit('radius-change', newRadius);
});

// Time control state - preserve the period selection
const selectedPeriod = ref(props.selectedDate ? 1 : 1); // Default to current day
const customDateRange = ref(false);
const isYesterday = ref(false);

// Remember the last selection
const lastTimeSelection = ref<{type: 'period' | 'yesterday' | 'custom', value: any}>({
    type: 'period',
    value: 1
});

// Date range state
const today = new Date().toISOString().split('T')[0];
const startDate = ref(props.selectedDate || today);
const endDate = ref(today);

// Initialize date range based on default period
const initializeDateRange = () => {
    const end = new Date();
    const start = new Date();
    
    if (selectedPeriod.value === 1) {
        // Today only
        start.setDate(start.getDate());
    } else {
        // Multiple days
        start.setDate(start.getDate() - selectedPeriod.value + 1);
    }
    
    startDate.value = start.toISOString().split('T')[0];
    endDate.value = end.toISOString().split('T')[0];
};

// Restore saved time selection on mount
const restoreSavedTimeSelection = () => {
    const saved = localStorage.getItem('stopEfficiencyTimeSelection');
    if (saved) {
        try {
            const savedSelection = JSON.parse(saved);
            lastTimeSelection.value = savedSelection;
            
            // Restore the selection
            if (savedSelection.type === 'yesterday') {
                selectYesterday();
            } else if (savedSelection.type === 'custom' && savedSelection.value) {
                startDate.value = savedSelection.value.start;
                endDate.value = savedSelection.value.end;
                customDateRange.value = true;
                emit('date-range-change', startDate.value, endDate.value);
            } else if (savedSelection.type === 'period' && savedSelection.value) {
                selectQuickPeriod(savedSelection.value);
            }
        } catch (e) {
            console.error('Failed to restore time selection:', e);
            initializeDateRange();
        }
    } else {
        initializeDateRange();
    }
};

// Initialize on mount
onMounted(() => {
    restoreSavedTimeSelection();
});

// Quick period selection
const selectQuickPeriod = (days: number) => {
    selectedPeriod.value = days;
    customDateRange.value = false;
    isYesterday.value = false;
    lastTimeSelection.value = { type: 'period', value: days };
    localStorage.setItem('stopEfficiencyTimeSelection', JSON.stringify(lastTimeSelection.value));
    initializeDateRange();
    emit('period-change', days);
};

// Yesterday selection
const selectYesterday = () => {
    isYesterday.value = true;
    customDateRange.value = false;
    selectedPeriod.value = 1;
    lastTimeSelection.value = { type: 'yesterday', value: null };
    localStorage.setItem('stopEfficiencyTimeSelection', JSON.stringify(lastTimeSelection.value));
    
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayString = yesterday.toISOString().split('T')[0];
    
    startDate.value = yesterdayString;
    endDate.value = yesterdayString;
    
    emit('date-range-change', yesterdayString, yesterdayString);
};

// Apply custom date range
const applyCustomDateRange = () => {
    if (startDate.value && endDate.value) {
        customDateRange.value = true;
        isYesterday.value = false;
        lastTimeSelection.value = { type: 'custom', value: { start: startDate.value, end: endDate.value } };
        localStorage.setItem('stopEfficiencyTimeSelection', JSON.stringify(lastTimeSelection.value));
        emit('date-range-change', startDate.value, endDate.value);
    }
};

// Filter vehicles based on selected devices
const filteredVehicles = computed(() => 
    props.devices.filter(device => props.selectedDeviceIds.includes(device.id))
);

// Display date range text
const displayDateRange = computed(() => {
    // Format dates for display
    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    };
    
    // Check if yesterday is selected
    if (isYesterday.value) {
        return `เมื่อวาน (${formatDate(startDate.value)})`;
    }
    
    // Check if custom date range
    if (customDateRange.value && startDate.value !== endDate.value) {
        return `${formatDate(startDate.value)} - ${formatDate(endDate.value)}`;
    }
    
    // Check if it's a multi-day period
    if (selectedPeriod.value > 1 && !customDateRange.value) {
        return `${selectedPeriod.value} วันล่าสุด (${formatDate(startDate.value)} - ${formatDate(endDate.value)})`;
    }
    
    // Single day (today)
    if (selectedPeriod.value === 1 && !customDateRange.value && !isYesterday.value) {
        return `วันนี้ (${formatDate(today)})`;
    }
    
    // Default to showing the date range
    if (startDate.value === endDate.value) {
        return formatDate(startDate.value);
    }
    
    return `${formatDate(startDate.value)} - ${formatDate(endDate.value)}`;
});

// Helper function to check if stop is at office
const isOfficeStop = (stop: any): boolean => {
    // Check by location name
    if (stop.location && (
        stop.location.includes('สำนักงาน') || 
        stop.location.includes('Office') ||
        stop.location.includes('สมชายฟาร์ม')
    )) {
        return true;
    }
    
    // Check by coordinates
    const distance = calculateDistance(
        stop.latitude,
        stop.longitude,
        officeCoordinates.lat,
        officeCoordinates.lng
    );
    return distance <= 100; // 100 meters radius around office
};

// Helper function to check if stop is at pickup location
const isPickupStop = (stop: any): boolean => {
    // Check by location name
    if (stop.location) {
        // Check for mill keywords
        if (stop.location.includes('โรงสี')) {
            return true;
        }
        // Check against known pickup location names
        for (const pickup of pickupLocations) {
            if (stop.location.includes(pickup.name) || pickup.name.includes(stop.location)) {
                return true;
            }
        }
    }
    
    // Check by coordinates against known pickup locations
    for (const pickup of pickupLocations) {
        const distance = calculateDistance(
            stop.latitude,
            stop.longitude,
            pickup.lat,
            pickup.lng
        );
        if (distance <= stopRadius.value) { // Use user-adjustable radius
            return true;
        }
    }
    return false;
};

// Helper function to check if stop is at delivery location
const isDeliveryStop = (stop: any): boolean => {
    // Check by location name
    if (stop.location) {
        // Common delivery keywords
        const deliveryKeywords = ['ฟาร์ม', 'Farm', 'การเกษตร', 'วิสาหกิจ', 'เบทาโกร'];
        for (const keyword of deliveryKeywords) {
            if (stop.location.includes(keyword)) {
                return true;
            }
        }
        // Check against known delivery location names
        for (const delivery of deliveryLocations) {
            if (stop.location.includes(delivery.name) || delivery.name.includes(stop.location)) {
                return true;
            }
        }
    }
    
    // Check by coordinates against known delivery locations
    for (const delivery of deliveryLocations) {
        const distance = calculateDistance(
            stop.latitude,
            stop.longitude,
            delivery.lat,
            delivery.lng
        );
        if (distance <= stopRadius.value) { // Use user-adjustable radius
            return true;
        }
    }
    return false;
};

// Get stop location type
const getStopLocationType = (stop: any): string => {
    if (isOfficeStop(stop)) return 'สำนักงาน';
    if (isPickupStop(stop)) return 'จุดรับสินค้า';
    if (isDeliveryStop(stop)) return 'จุดส่งสินค้า';
    return 'จุดอื่นๆ';
};

// Calculate stop statistics (excluding office stops)
const stopStats = computed(() => {
    const stats = {
        totalStops: 0,
        totalDuration: 0,
        inefficientCount: 0,
        stopsByVehicle: {} as Record<number, any>
    };

    props.routeHistory.forEach(stop => {
        if (!props.selectedDeviceIds.includes(stop.deviceId)) return;
        
        // Skip office stops
        if (isOfficeStop(stop)) return;
        
        stats.totalStops++;
        stats.totalDuration += stop.duration;
        
        // Consider stops > 60 minutes as inefficient
        if (stop.duration > 60) {
            stats.inefficientCount++;
        }
        
        if (!stats.stopsByVehicle[stop.deviceId]) {
            stats.stopsByVehicle[stop.deviceId] = {
                stops: [],
                totalDuration: 0,
                count: 0
            };
        }
        
        stats.stopsByVehicle[stop.deviceId].stops.push(stop);
        stats.stopsByVehicle[stop.deviceId].totalDuration += stop.duration;
        stats.stopsByVehicle[stop.deviceId].count++;
    });

    return stats;
});

// Summary metrics
const totalStops = computed(() => stopStats.value.totalStops);
const averageStopDuration = computed(() => {
    if (stopStats.value.totalStops === 0) return '0:00';
    const avgMinutes = Math.round(stopStats.value.totalDuration / stopStats.value.totalStops);
    return formatDuration(avgMinutes);
});
const inefficientStops = computed(() => stopStats.value.inefficientCount);
const longestStopDuration = computed(() => {
    if (stopStats.value.totalStops === 0) return '-';
    
    // Find the longest stop from all vehicles
    let maxDuration = 0;
    Object.values(stopStats.value.stopsByVehicle).forEach((vehicleStats: any) => {
        if (vehicleStats.stops && vehicleStats.stops.length > 0) {
            const vehicleMax = Math.max(...vehicleStats.stops.map((s: any) => s.duration));
            if (vehicleMax > maxDuration) {
                maxDuration = vehicleMax;
            }
        }
    });
    
    if (maxDuration === 0) return '-';
    
    const hours = Math.floor(maxDuration / 60);
    const mins = maxDuration % 60;
    return `${hours} ชม. ${mins} น.`;
});

// Stop duration distribution (excluding office stops)
const stopDurationData = computed(() => 
    props.routeHistory.filter(stop => 
        props.selectedDeviceIds.includes(stop.deviceId) && !isOfficeStop(stop)
    )
);

const stopDurationBuckets = computed(() => {
    const buckets = [
        { min: 15, max: 30, label: '15-30m', count: 0 },
        { min: 30, max: 60, label: '30-60m', count: 0 },
        { min: 60, max: 120, label: '1-2h', count: 0 },
        { min: 120, max: 180, label: '2-3h', count: 0 },
        { min: 180, max: Infinity, label: '3h+', count: 0 }
    ];
    
    stopDurationData.value.forEach(stop => {
        const bucket = buckets.find(b => stop.duration >= b.min && stop.duration < b.max);
        if (bucket) bucket.count++;
    });
    
    return buckets;
});

const maxBucketCount = computed(() => Math.max(...stopDurationBuckets.value.map(b => b.count), 1));

// Vehicle statistics analysis
const vehicleStatistics = computed(() => {
    return filteredVehicles.value.map(vehicle => {
        const vehicleStats = stopStats.value.stopsByVehicle[vehicle.id];
        
        // Handle vehicles with no stops
        if (!vehicleStats || vehicleStats.count === 0) {
            return {
                id: vehicle.id,
                name: vehicle.name,
                color: props.getVehicleColor(props.selectedDeviceIds, vehicle.id),
                totalStops: 0,
                totalDuration: '-',
                avgDuration: '-'
            };
        }
        
        const totalHours = Math.floor(vehicleStats.totalDuration / 60);
        const totalMins = vehicleStats.totalDuration % 60;
        const totalDuration = `${totalHours} ชม. ${totalMins} น.`;
        const avgDuration = formatDuration(Math.round(vehicleStats.totalDuration / vehicleStats.count));
        
        return {
            id: vehicle.id,
            name: vehicle.name,
            color: props.getVehicleColor(props.selectedDeviceIds, vehicle.id),
            totalStops: vehicleStats.count,
            totalDuration,
            avgDuration
        };
    }).sort((a, b) => b.totalStops - a.totalStops); // Sort by total stops descending
});

// All inefficient stops (for count)
const allInefficientStops = computed(() => {
    return props.routeHistory
        .filter(stop => 
            props.selectedDeviceIds.includes(stop.deviceId) && 
            stop.duration > 60 && 
            !isOfficeStop(stop)
        )
        .sort((a, b) => b.duration - a.duration);
});

// Count for pagination
const inefficientStopsCount = computed(() => allInefficientStops.value.length);

// Paginated inefficient stops details
const inefficientStopDetails = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    
    return allInefficientStops.value
        .slice(start, end)
        .map(stop => ({
            ...stop,
            vehicleColor: props.getVehicleColor(props.selectedDeviceIds, stop.deviceId)
        }));
});

// Reset page when data changes
watch([() => props.selectedDeviceIds, () => props.routeHistory], () => {
    currentPage.value = 1;
});

// Helper functions
const formatDuration = (minutes: number): string => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
};

const formatStartTimeWithDayOfWeek = (startTime: string): string => {
    // Thai day names
    const thaiDays = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
    
    try {
        // Handle ISO format: "2025-08-17 11:57:33"
        if (startTime.includes('-') && startTime.includes(' ') && startTime.includes(':')) {
            const [dateStr, timeStr] = startTime.split(' ');
            const [year, month, day] = dateStr.split('-');
            const [hour, minute] = timeStr.split(':');
            
            const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            const dayName = thaiDays[date.getDay()];
            
            return `${dayName} ${hour}:${minute} ${day}/${month}/${year}`;
        }
        // Handle Thai format: "HH:MM DD/MM/YYYY"
        else if (startTime.includes(' ') && startTime.includes('/')) {
            const [time, dateStr] = startTime.split(' ');
            const [day, month, year] = dateStr.split('/');
            const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            const dayName = thaiDays[date.getDay()];
            return `${dayName} ${time} ${dateStr}`;
        } 
        // Handle other formats with /
        else if (startTime.includes('/')) {
            const parts = startTime.split(' ');
            const dateStr = parts[0];
            const time = parts[1] || '';
            const [day, month, year] = dateStr.split('/');
            const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            const dayName = thaiDays[date.getDay()];
            return time ? `${dayName} ${time} ${dateStr}` : `${dayName} ${dateStr}`;
        } 
        else {
            // Fallback: return as is
            return startTime;
        }
    } catch (error) {
        console.error('Error formatting start time:', error, startTime);
        return startTime;
    }
};

const getStopDurationClass = (duration: number): string => {
    // Match the map legend colors
    if (duration < 15) return 'bg-gray-100 text-gray-800';    // Gray for short stops
    if (duration < 60) return 'bg-orange-100 text-orange-800'; // Orange for normal stops
    if (duration < 120) return 'bg-red-100 text-red-800';      // Red for long stops
    return 'bg-purple-100 text-purple-800';                    // Purple for very long stops
};

const getLocationTypeClass = (stop: any): string => {
    const type = getStopLocationType(stop);
    if (type === 'สำนักงาน') return 'bg-red-100 text-red-800';
    if (type === 'จุดรับสินค้า') return 'bg-blue-100 text-blue-800';
    if (type === 'จุดส่งสินค้า') return 'bg-green-100 text-green-800';
    return 'bg-gray-100 text-gray-800'; // จุดอื่นๆ
};

// Get color for bucket bars
const getBucketColor = (minDuration: number): string => {
    if (minDuration < 15) return '#9CA3AF';   // Gray for short stops
    if (minDuration < 30) return '#FFA500';   // Orange for normal stops
    if (minDuration < 60) return '#EF4444';   // Red for longer stops
    if (minDuration < 120) return '#DC2626';  // Dark red for long stops
    return '#7C3AED';                         // Purple for very long stops
};
</script>