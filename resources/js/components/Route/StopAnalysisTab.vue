<template>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">การวิเคราะห์จุดจอด</h2>
                <p class="text-sm text-gray-600">วิเคราะห์รูปแบบและระยะเวลาการจอดของยานพาหนะ</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ displayDateRange }} | {{ filteredVehicles.length }} คัน
            </div>
        </div>

        <!-- Single Line Controls -->
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <!-- Time Period -->
                <span class="font-medium text-gray-700">เวลา:</span>
                <Button 
                    @click="selectYesterday()"
                    :variant="isYesterday && !customDateRange ? 'default' : 'outline'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                >
                    เมื่อวาน
                </Button>
                <Button 
                    @click="selectQuickPeriod(1)"
                    :variant="selectedPeriod === 1 && !customDateRange && !isYesterday ? 'default' : 'outline'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                >
                    วันนี้
                </Button>
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
                
                <!-- Custom Date Range -->
                <input
                    type="date"
                    v-model="startDate"
                    :max="endDate"
                    class="h-7 px-2 text-sm border border-gray-300 rounded"
                />
                <span class="text-gray-500">-</span>
                <input
                    type="date"
                    v-model="endDate"
                    :min="startDate"
                    :max="today"
                    class="h-7 px-2 text-sm border border-gray-300 rounded"
                />
                <Button 
                    @click="applyCustomDateRange"
                    :variant="customDateRange ? 'default' : 'outline'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                >
                    ใช้
                </Button>
                
                <!-- Separator -->
                <span class="text-gray-400 mx-1">|</span>
                
                <!-- Radius -->
                <span class="font-medium text-gray-700">รัศมี:</span>
                <input
                    type="range"
                    v-model.number="routeAnalysisRadius"
                    min="50"
                    max="1000"
                    step="50"
                    class="w-20"
                />
                <input
                    type="number"
                    v-model.number="routeAnalysisRadius"
                    min="50"
                    max="1000"
                    step="50"
                    class="w-16 px-2 py-1 text-sm border border-gray-300 rounded"
                />
                <span class="text-gray-600">ม</span>
                
                <!-- Separator -->
                <span class="text-gray-400 mx-1">|</span>
                
                <!-- Point Type -->
                <span class="font-medium text-gray-700">ประเภท:</span>
                <Button
                    @click="togglePointTypeFilter('all')"
                    :variant="pointTypeFilter === 'all' ? 'default' : 'outline'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                >
                    ทั้งหมด
                </Button>
                <Button
                    @click="togglePointTypeFilter('known')"
                    :variant="pointTypeFilter === 'known' ? 'default' : 'outline'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                >
                    รู้จัก({{ knownLocationCount }})
                </Button>
                <Button
                    @click="togglePointTypeFilter('unknown')"
                    :variant="pointTypeFilter === 'unknown' ? 'default' : 'outline'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                >
                    ไม่รู้จัก({{ unknownLocationCount }})
                </Button>
                <Button
                    @click="togglePointTypeFilter('office')"
                    :variant="pointTypeFilter === 'office' ? 'default' : 'outline'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                >
                    สำนักงาน({{ officeLocationCount }})
                </Button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">จำนวนจุดจอดทั้งหมด</div>
                <div class="text-2xl font-bold text-blue-600">{{ totalStops }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">เวลาจอดเฉลี่ย</div>
                <div class="text-2xl font-bold text-green-600">{{ averageStopDuration }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">จุดจอดผิดปกติ</div>
                <div class="text-2xl font-bold text-orange-600">{{ inefficientStops }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">จอดนานสุด</div>
                <div class="text-2xl font-bold text-gray-700">{{ longestStopDuration }}</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Stop Duration Distribution -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-medium mb-4">การกระจายของระยะเวลาจอด (15 นาทีขึ้นไป)</h3>
                <div class="h-64 flex items-center justify-center text-gray-400">
                    <!-- Chart placeholder -->
                    <div v-if="stopDurationData.length === 0" class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <div class="mt-2">ไม่มีข้อมูลการจอด</div>
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
                            จำนวนจุดจอดแยกตามระยะเวลา
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stop Statistics by Location -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-medium mb-4">สถิติการจอดแยกตามจุด</h3>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <div 
                        v-for="location in locationStatistics" 
                        :key="`${location.latitude}-${location.longitude}`"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded cursor-pointer hover:bg-gray-100"
                        @click="filterByLocation(location.latitude, location.longitude)"
                    >
                        <div class="flex items-center gap-3">
                            <div 
                                class="w-4 h-4 rounded-full"
                                :style="{ backgroundColor: location.color }"
                            ></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ location.location }}</div>
                                <div class="text-sm text-gray-600">{{ location.type }} • {{ location.totalStops }} ครั้ง • {{ location.vehicleCount }} คัน</div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-semibold text-gray-700">
                                นานสุด {{ location.maxDuration }}
                            </div>
                            <div class="text-sm text-gray-600">เฉลี่ย {{ location.avgDuration }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duration-based Map Visualization -->
        <div class="bg-white p-6 rounded-lg border shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">แผนที่จุดจอดตามระยะเวลา</h3>
                <div class="text-sm text-gray-600">
                    {{ allInefficientStops.length }} จุดจอด
                </div>
            </div>
            
            <!-- Duration Filter Controls -->
            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="text-sm font-medium text-gray-700">แสดงจุดจอด:</span>
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="toggleDurationFilter('all')"
                            :class="[
                                durationFilter === 'all' 
                                    ? 'bg-gray-600 text-white' 
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                'px-3 py-1 text-sm rounded-md font-medium'
                            ]"
                        >
                            ทั้งหมด ({{ inefficientStopsCount }})
                        </button>
                        <button
                            @click="toggleDurationFilter('short')"
                            :class="[
                                durationFilter === 'short' 
                                    ? 'bg-green-600 text-white' 
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                'px-3 py-1 text-sm rounded-md font-medium'
                            ]"
                        >
                            สั้น 5-60น. ({{ shortStopsCount }})
                        </button>
                        <button
                            @click="toggleDurationFilter('medium')"
                            :class="[
                                durationFilter === 'medium' 
                                    ? 'bg-yellow-600 text-white' 
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                'px-3 py-1 text-sm rounded-md font-medium'
                            ]"
                        >
                            ปานกลาง 1-2ชม. ({{ mediumStopsCount }})
                        </button>
                        <button
                            @click="toggleDurationFilter('long')"
                            :class="[
                                durationFilter === 'long' 
                                    ? 'bg-red-600 text-white' 
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                'px-3 py-1 text-sm rounded-md font-medium'
                            ]"
                        >
                            นาน 2ชม.+ ({{ longStopsCount }})
                        </button>
                    </div>
                </div>
                
                <!-- Map Legend -->
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <span class="text-sm font-medium text-gray-700 mr-4">สีตามระยะเวลา:</span>
                    <div class="flex flex-wrap gap-4 mt-2">
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                            <span class="text-xs text-gray-600">5-15 นาที</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-xs text-gray-600">15-60 นาที</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <span class="text-xs text-gray-600">1-2 ชั่วโมง</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <span class="text-xs text-gray-600">2 ชั่วโมง+</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded-full bg-purple-500 border-2 border-purple-300"></div>
                            <span class="text-xs text-gray-600">ที่สำนักงาน</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Location Filter Indicator -->
            <div v-if="selectedLocationFilter" class="bg-blue-50 border border-blue-200 p-3 rounded-lg mb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div class="text-sm">
                            <span class="font-medium text-blue-800">กรองตามตำแหน่ง:</span>
                            <span class="text-blue-700">
                                {{ selectedLocationFilter.latitude.toFixed(6) }}, {{ selectedLocationFilter.longitude.toFixed(6) }} 
                                (รัศมี {{ selectedLocationFilter.radius }}m)
                            </span>
                        </div>
                    </div>
                    <button 
                        @click="clearLocationFilter"
                        class="text-blue-600 hover:text-blue-800 font-medium text-sm px-2 py-1 rounded hover:bg-blue-100"
                    >
                        ยกเลิกการกรอง
                    </button>
                </div>
            </div>
            
            <!-- Map Container -->
            <div class="relative">
                <div 
                    ref="durationMapContainer"
                    class="w-full h-96 rounded-lg border"
                ></div>
                <div v-if="!filteredMapStops.length" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                    <div class="text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <div class="mt-2">ไม่มีข้อมูลจุดจอดที่เลือก</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Stop Analysis -->
        <div class="bg-white p-6 rounded-lg border shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">{{ stopAnalysisTitle }}</h3>
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
                                    @click="showStopOnMap(stop)"
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
                    <Button
                        @click="currentPage--"
                        :disabled="currentPage === 1"
                        variant="outline"
                        size="sm"
                        class="h-8 px-3"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Button>
                    
                    <!-- Page numbers -->
                    <template v-for="page in totalPages" :key="page">
                        <Button
                            v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                            @click="currentPage = page"
                            :variant="currentPage === page ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 px-3"
                        >
                            {{ page }}
                        </Button>
                        <span 
                            v-else-if="page === currentPage - 2 || page === currentPage + 2"
                            class="px-2 text-gray-500"
                        >
                            ...
                        </span>
                    </template>
                    
                    <!-- Next button -->
                    <Button
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        variant="outline"
                        size="sm"
                        class="h-8 px-3"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, nextTick } from 'vue';
import { officeCoordinates, calculateDistance, pickupLocations, deliveryLocations, LOCATION_RADIUS } from '@/composables/useRouteFiltering';
import { useStopAnalysis } from '@/composables/route/useStopAnalysis';
import { Button } from '@/components/ui/button';

interface Props {
    selectedDeviceIds: number[];
    selectedDate: string;
    devices: Array<{ id: number; name: string; type: string }>;
    routeHistory: any[];
    getVehicleColor: (deviceId: number) => string;
    routeAnalysisRadius: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'period-change': [days: number];
    'date-range-change': [startDate: string, endDate: string];
    'update:routeAnalysisRadius': [radius: number];
}>();

const { analyzeRouteStops, processStops } = useStopAnalysis();

// Use v-model for route analysis radius
const routeAnalysisRadius = computed({
    get: () => props.routeAnalysisRadius,
    set: (value) => emit('update:routeAnalysisRadius', value)
});

// Point type filter state
const pointTypeFilter = ref<'all' | 'known' | 'unknown' | 'office'>('all');

// Location filter state - for filtering by clicked map location
const selectedLocationFilter = ref<{latitude: number, longitude: number, radius: number} | null>(null);

// Duration filter state for map
const durationFilter = ref<'all' | 'short' | 'medium' | 'long'>('all');

// Map state
const durationMapContainer = ref<HTMLElement | null>(null);
const durationMap = ref<any>(null);
const mapMarkers = ref<any[]>([]);
const selectedStopMarker = ref<any>(null);

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalPages = computed(() => Math.ceil(inefficientStopsCount.value / itemsPerPage.value));

// No longer needed - using v-model instead

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
        officeCoordinates.value.lat,
        officeCoordinates.value.lng
    );
    return distance <= LOCATION_RADIUS;
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
        for (const pickup of pickupLocations.value) {
            if (stop.location.includes(pickup.name) || pickup.name.includes(stop.location)) {
                return true;
            }
        }
    }
    
    // Check by coordinates against known pickup locations
    for (const pickup of pickupLocations.value) {
        const distance = calculateDistance(
            stop.latitude,
            stop.longitude,
            pickup.lat,
            pickup.lng
        );
        if (distance <= routeAnalysisRadius.value) { // Use user-adjustable radius
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
        for (const delivery of deliveryLocations.value) {
            if (stop.location.includes(delivery.name) || delivery.name.includes(stop.location)) {
                return true;
            }
        }
    }
    
    // Check by coordinates against known delivery locations
    for (const delivery of deliveryLocations.value) {
        const distance = calculateDistance(
            stop.latitude,
            stop.longitude,
            delivery.lat,
            delivery.lng
        );
        if (distance <= routeAnalysisRadius.value) { // Use user-adjustable radius
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

// Helper function to determine if stop is a known location
const isKnownLocation = (stop: any): boolean => {
    return isOfficeStop(stop) || isPickupStop(stop) || isDeliveryStop(stop);
};

// Point type filter toggle function
const togglePointTypeFilter = (type: 'all' | 'known' | 'unknown' | 'office') => {
    pointTypeFilter.value = type;
};

// Calculate distance between two coordinates (Haversine formula)
const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number): number => {
    const R = 6371000; // Earth's radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; // Distance in meters
};

// Function to filter by clicked location
const filterByLocation = (latitude: number, longitude: number, radius?: number) => {
    const filterRadius = radius || routeAnalysisRadius.value;
    selectedLocationFilter.value = { latitude, longitude, radius: filterRadius };
    
    // Reset point type filter to show all when filtering by location
    pointTypeFilter.value = 'all';
};

// Clear location filter
const clearLocationFilter = () => {
    selectedLocationFilter.value = null;
};

// Filtered route history based on point type and location
const filteredRouteHistory = computed(() => {
    let filtered = props.routeHistory;
    
    // First filter by device IDs
    filtered = filtered.filter(stop => props.selectedDeviceIds.includes(stop.deviceId));
    
    // Filter by location if location filter is active
    if (selectedLocationFilter.value) {
        const { latitude, longitude, radius } = selectedLocationFilter.value;
        filtered = filtered.filter(stop => {
            const distance = calculateDistance(
                parseFloat(stop.latitude), 
                parseFloat(stop.longitude),
                latitude, 
                longitude
            );
            return distance <= radius;
        });
    }
    
    // Then filter by point type
    if (pointTypeFilter.value !== 'all') {
        filtered = filtered.filter(stop => {
            switch (pointTypeFilter.value) {
                case 'known':
                    return isKnownLocation(stop) && !isOfficeStop(stop);
                case 'unknown':
                    return !isKnownLocation(stop);
                case 'office':
                    return isOfficeStop(stop);
                default:
                    return true;
            }
        });
    }
    
    return filtered;
});

// Count different types of locations
const knownLocationCount = computed(() => {
    return props.routeHistory.filter(stop => 
        props.selectedDeviceIds.includes(stop.deviceId) && 
        isKnownLocation(stop) && 
        !isOfficeStop(stop)
    ).length;
});

const unknownLocationCount = computed(() => {
    return props.routeHistory.filter(stop => 
        props.selectedDeviceIds.includes(stop.deviceId) && 
        !isKnownLocation(stop)
    ).length;
});

const officeLocationCount = computed(() => {
    return props.routeHistory.filter(stop => 
        props.selectedDeviceIds.includes(stop.deviceId) && 
        isOfficeStop(stop)
    ).length;
});

// Dynamic title for stop analysis table
const stopAnalysisTitle = computed(() => {
    switch (durationFilter.value) {
        case 'short':
            return 'รายละเอียดจุดจอดสั้น (5-60 นาที)';
        case 'medium':
            return 'รายละเอียดจุดจอดปานกลาง (1-2 ชั่วโมง)';
        case 'long':
            return 'รายละเอียดจุดจอดนาน (2+ ชั่วโมง)';
        case 'all':
            return 'รายละเอียดจุดจอด (ทุกระยะเวลา)';
        default:
            return 'รายละเอียดจุดจอดนาน (>60 นาที)';
    }
});

// Map data computed properties - all stops for the "all" filter (but still >= 5 minutes)
const allStopsForMap = computed(() => {
    return filteredRouteHistory.value.filter(stop => 
        props.selectedDeviceIds.includes(stop.deviceId) && 
        stop.duration >= 5 // Only show stops >= 5 minutes
    );
});

// Stops >= 5 minutes for duration-based filters (same as allStopsForMap now)
const allMapStops = computed(() => {
    return allStopsForMap.value;
});

const shortStopsCount = computed(() => 
    allMapStops.value.filter(stop => 
        stop.duration >= 5 && stop.duration < 60 &&
        (pointTypeFilter.value === 'office' || !isOfficeStop(stop))
    ).length
);

const mediumStopsCount = computed(() => 
    allMapStops.value.filter(stop => 
        stop.duration >= 60 && stop.duration < 120 &&
        (pointTypeFilter.value === 'office' || !isOfficeStop(stop))
    ).length
);

const longStopsCount = computed(() => 
    allMapStops.value.filter(stop => 
        stop.duration >= 120 &&
        (pointTypeFilter.value === 'office' || !isOfficeStop(stop))
    ).length
);

const filteredMapStops = computed(() => {
    if (durationFilter.value === 'all') {
        return allStopsForMap.value; // Show all stops >= 5 minutes
    }
    
    return allMapStops.value.filter(stop => {
        switch (durationFilter.value) {
            case 'short':
                return stop.duration >= 5 && stop.duration < 60;
            case 'medium':
                return stop.duration >= 60 && stop.duration < 120;
            case 'long':
                return stop.duration >= 120;
            default:
                return true;
        }
    });
});

// Calculate stop statistics (using filtered data)
const stopStats = computed(() => {
    const stats = {
        totalStops: 0,
        totalDuration: 0,
        inefficientCount: 0,
        stopsByVehicle: {} as Record<number, any>
    };

    filteredRouteHistory.value.forEach(stop => {
        if (!props.selectedDeviceIds.includes(stop.deviceId)) return;
        
        // For office filter, include office stops; for others, skip them
        if (pointTypeFilter.value !== 'office' && isOfficeStop(stop)) return;
        
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

// Stop duration distribution (using filtered data)
const stopDurationData = computed(() => 
    filteredRouteHistory.value.filter(stop => 
        props.selectedDeviceIds.includes(stop.deviceId) && 
        (pointTypeFilter.value === 'office' || !isOfficeStop(stop))
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

// Location statistics analysis
const locationStatistics = computed(() => {
    const locationGroups = [];
    
    // Group stops by location using radius-based clustering
    filteredRouteHistory.value.forEach(stop => {
        if (!props.selectedDeviceIds.includes(stop.deviceId)) return;
        
        const stopLat = parseFloat(stop.latitude);
        const stopLng = parseFloat(stop.longitude);
        
        // Find existing group within radius
        let foundGroup = locationGroups.find(group => {
            const distance = calculateDistance(stopLat, stopLng, group.latitude, group.longitude);
            return distance <= routeAnalysisRadius.value;
        });
        
        if (!foundGroup) {
            // Create new group
            foundGroup = {
                location: stop.location,
                latitude: stopLat,
                longitude: stopLng,
                stops: [],
                vehicles: new Set()
            };
            locationGroups.push(foundGroup);
        } else {
            // Use the most descriptive location name (prefer non-coordinate names)
            if (foundGroup.location.includes(',') && !stop.location.includes(',')) {
                foundGroup.location = stop.location;
            }
        }
        
        foundGroup.stops.push(stop);
        foundGroup.vehicles.add(stop.deviceId);
    });
    
    // Convert to array and calculate statistics
    const locations = locationGroups.map(locationData => {
        const durations = locationData.stops.map(stop => stop.duration);
        const maxDuration = Math.max(...durations);
        const totalDuration = locationData.stops.reduce((sum, stop) => sum + stop.duration, 0);
        const avgDuration = formatDuration(Math.round(totalDuration / locationData.stops.length));
        const maxDurationStr = formatDuration(maxDuration);
        
        // Determine location type for color coding
        const isOffice = isOfficeStop(locationData.stops[0]);
        const isKnown = isKnownLocation(locationData.stops[0]);
        let color = '#6B7280'; // Gray for unknown
        
        if (isOffice) {
            color = '#8B5CF6'; // Purple for office
        } else if (isKnown) {
            color = '#10B981'; // Green for known locations
        } else {
            color = '#F59E0B'; // Yellow for unknown
        }
        
        return {
            location: locationData.location,
            latitude: locationData.latitude,
            longitude: locationData.longitude,
            totalStops: locationData.stops.length,
            vehicleCount: locationData.vehicles.size,
            maxDuration: maxDurationStr,
            avgDuration,
            color,
            type: getStopLocationType(locationData.stops[0])
        };
    }).sort((a, b) => b.totalStops - a.totalStops); // Sort by total stops descending
    
    return locations;
});

// All inefficient stops (synced with map duration filter)
const allInefficientStops = computed(() => {
    let stops = filteredRouteHistory.value.filter(stop => 
        props.selectedDeviceIds.includes(stop.deviceId) &&
        stop.duration >= 5 // Only show stops >= 5 minutes
    );
    
    // Apply duration filtering based on map filter
    if (durationFilter.value === 'all') {
        // For 'all', show truly all stops (no duration filter)
        stops = stops.filter(stop => 
            (pointTypeFilter.value === 'office' || !isOfficeStop(stop))
        );
    } else {
        // Sync with map duration filter
        stops = stops.filter(stop => {
            switch (durationFilter.value) {
                case 'short':
                    return stop.duration >= 5 && stop.duration < 60;
                case 'medium':
                    return stop.duration >= 60 && stop.duration < 120;
                case 'long':
                    return stop.duration >= 120;
                default:
                    return stop.duration > 60; // Fallback
            }
        });
        
        // For non-'all' filters, respect office filter
        if (pointTypeFilter.value !== 'office') {
            stops = stops.filter(stop => !isOfficeStop(stop));
        }
    }
    
    return stops.sort((a, b) => b.duration - a.duration);
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
watch([() => props.selectedDeviceIds, () => props.routeHistory, pointTypeFilter, durationFilter, selectedLocationFilter], () => {
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

// Map functions
const toggleDurationFilter = (filter: 'all' | 'short' | 'medium' | 'long') => {
    durationFilter.value = filter;
    updateMapMarkers();
};

const showStopOnMap = async (stop: any) => {
    if (!durationMap.value) {
        // If map not initialized, initialize it first
        await initializeMap();
    }
    
    const L = await import('leaflet');
    
    // Remove existing selected stop marker
    if (selectedStopMarker.value) {
        durationMap.value.removeLayer(selectedStopMarker.value);
        selectedStopMarker.value = null;
    }
    
    // Pan to the stop location
    durationMap.value.setView([stop.latitude, stop.longitude], 17);
    
    // Create highlight circle
    selectedStopMarker.value = L.circle([stop.latitude, stop.longitude], {
        color: '#FFD700',
        fillColor: '#FFD700',
        fillOpacity: 0.3,
        radius: 100, // 100 meter radius
        weight: 3
    });
    
    // Get vehicle info
    const vehicle = props.devices.find(d => d.id === stop.deviceId);
    const vehicleName = vehicle?.name || `รถ ID: ${stop.deviceId}`;
    
    selectedStopMarker.value.bindPopup(`
        <div style="font-size: 12px;">
            <strong>จุดจอดที่เลือก</strong><br>
            ${stop.location}<br>
            รถ: ${vehicleName}<br>
            เวลา: ${formatStartTimeWithDayOfWeek(stop.startTime)}<br>
            ระยะเวลา: ${formatDuration(stop.duration)}<br>
            ประเภท: ${getStopLocationType(stop)}<br>
            พิกัด: ${parseFloat(stop.latitude).toFixed(6)}, ${parseFloat(stop.longitude).toFixed(6)}
        </div>
    `);
    
    selectedStopMarker.value.addTo(durationMap.value).openPopup();
};

const getDurationColor = (duration: number): string => {
    if (duration >= 5 && duration < 15) return '#6B7280'; // Gray for very short (5-15 min)
    if (duration >= 15 && duration < 60) return '#10B981'; // Green for short (15-60 min)
    if (duration >= 60 && duration < 120) return '#F59E0B'; // Yellow for medium (1-2 hours)
    if (duration >= 120) return '#EF4444'; // Red for long (2+ hours)
    return '#6B7280'; // Gray for others (fallback)
};

const initializeMap = async () => {
    if (!durationMapContainer.value) return;
    
    const L = await import('leaflet');
    
    // Initialize map
    durationMap.value = L.map(durationMapContainer.value).setView([7.61, 100.09], 10);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(durationMap.value);
    
    // Add office marker
    const officeMarker = L.circleMarker([officeCoordinates.value.lat, officeCoordinates.value.lng], {
        radius: 8,
        fillColor: '#8B5CF6',
        color: '#FFFFFF',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.8
    }).addTo(durationMap.value);
    
    officeMarker.bindPopup(`
        <div style="font-size: 12px;">
            <strong>สำนักงานสมชายฟาร์ม</strong><br>
            พิกัด: ${officeCoordinates.value.lat.toFixed(6)}, ${officeCoordinates.value.lng.toFixed(6)}
        </div>
    `);
    
    // Initial markers update
    updateMapMarkers();
};

const updateMapMarkers = async () => {
    if (!durationMap.value) return;
    
    const L = await import('leaflet');
    
    // Clear existing markers
    mapMarkers.value.forEach(marker => {
        durationMap.value.removeLayer(marker);
    });
    mapMarkers.value = [];
    
    // Sort stops by duration to layer them correctly (shortest first, office last)
    const sortedStops = [...allInefficientStops.value].sort((a, b) => {
        // Office stops always on top
        if (isOfficeStop(a) && !isOfficeStop(b)) return 1;
        if (!isOfficeStop(a) && isOfficeStop(b)) return -1;
        
        // Sort by duration (ascending - shortest first)
        return a.duration - b.duration;
    });
    
    // Add markers for filtered stops in layered order
    sortedStops.forEach(stop => {
        const color = isOfficeStop(stop) ? '#8B5CF6' : getDurationColor(stop.duration);
        const isOffice = isOfficeStop(stop);
        
        const marker = L.circleMarker([stop.latitude, stop.longitude], {
            radius: isOffice ? 8 : 6,
            fillColor: color,
            color: isOffice ? '#FFFFFF' : color,
            weight: isOffice ? 3 : 2,
            opacity: 1,
            fillOpacity: 0.7
        });
        
        // Get vehicle info
        const vehicle = props.devices.find(d => d.id === stop.deviceId);
        const vehicleName = vehicle?.name || `รถ ID: ${stop.deviceId}`;
        
        marker.bindPopup(`
            <div style="font-size: 12px;">
                <strong>${stop.location}</strong><br>
                รถ: ${vehicleName}<br>
                เวลา: ${formatStartTimeWithDayOfWeek(stop.startTime)}<br>
                ระยะเวลา: ${formatDuration(stop.duration)}<br>
                ประเภท: ${getStopLocationType(stop)}<br>
                พิกัด: ${parseFloat(stop.latitude).toFixed(6)}, ${parseFloat(stop.longitude).toFixed(6)}<br>
                <small style="color: #6B7280; font-style: italic;">คลิกจุดนี้เพื่อดูจุดจอดทั้งหมดในตำแหน่งนี้</small>
            </div>
        `);
        
        // Add click handler to filter table by this location
        marker.on('click', () => {
            filterByLocation(parseFloat(stop.latitude), parseFloat(stop.longitude));
        });
        
        marker.addTo(durationMap.value);
        mapMarkers.value.push(marker);
    });
    
    // Auto-fit map to show all markers
    if (allInefficientStops.value.length > 0) {
        const group = new L.featureGroup(mapMarkers.value);
        durationMap.value.fitBounds(group.getBounds().pad(0.1));
    }
};

// Watch for data changes to update map
watch([allInefficientStops, durationFilter], () => {
    if (durationMap.value) {
        updateMapMarkers();
    }
}, { deep: true });

// Initialize map on mount
onMounted(async () => {
    await nextTick();
    if (durationMapContainer.value) {
        setTimeout(() => {
            initializeMap();
        }, 100);
    }
});
</script>