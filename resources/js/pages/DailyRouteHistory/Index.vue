<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CalendarDays } from 'lucide-vue-next';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import RouteViewTab from '@/components/Route/RouteViewTab.vue';
import AnalyticsTab from '@/components/Route/AnalyticsTab.vue';
import { useRouteAPI } from '@/composables/route/useRouteAPI';
import { useVehicleConfig } from '@/composables/route/useVehicleConfig';
import { useCalendar } from '@/composables/route/useCalendar';
import { useRouteHistory } from '@/composables/route/useRouteHistory';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'ประวัติการเดินรถรายวัน',
        href: '/daily-route-history',
    },
];

// Initialize vehicle configuration composable
const {
    devices,
    vehicleColors,
    devicesByType,
    routeAnalysisRadius,
    officeHourStart,
    officeHourEnd,
    getVehicleColor,
    getDefaultDate
} = useVehicleConfig();

// Form data
const selectedDeviceIds = ref<number[]>([]);
const selectedDate = ref('');

// API State
const routeDataCollection = ref<Record<number, any>>({});

// Initialize Route API
const {
    isLoading,
    authorizationHeader,
    tokenHeader,
    fetchRouteData,
    fetchUtilizationData,
    getCachedData,
    setCachedData,
    clearOldCache,
    clearAllCache: clearIndexedDBCache,
    getCacheStats,
    initCache,
    cacheInitialized,
    cacheError
} = useRouteAPI();
// Tab management
const activeTab = ref('route-view');

// Analytics data
const utilizationData = ref<Record<string, any>>({});
const loadingUtilization = ref(false);

// Route visibility controls
const routeVisibility = ref<Record<number, boolean>>({});
// Cache status
const hasCachedDataForSelection = ref(false);
const cachedVehicleCountForSelection = ref(0);

const checkCacheStatus = async () => {
    if (selectedDeviceIds.value.length === 0 || !selectedDate.value || !cacheInitialized.value) {
        hasCachedDataForSelection.value = false;
        cachedVehicleCountForSelection.value = 0;
        return;
    }
    
    let count = 0;
    for (const deviceId of selectedDeviceIds.value) {
        const cached = await getCachedData(deviceId, selectedDate.value);
        if (cached) count++;
    }
    
    cachedVehicleCountForSelection.value = count;
    hasCachedDataForSelection.value = count > 0;
};

// Legacy computed properties for backward compatibility
const hasCachedData = computed(() => hasCachedDataForSelection.value);
const cachedVehicleCount = computed(() => cachedVehicleCountForSelection.value);

// IndexedDB cache stats only
const cacheStats = ref({
    indexedDB: { count: 0, estimatedSize: 0, oldestEntry: null, newestEntry: null }
});

const updateCacheStats = async () => {
    // Get IndexedDB stats only
    if (cacheInitialized.value) {
        try {
            cacheStats.value.indexedDB = await getCacheStats();
        } catch (error) {
            console.error('Error getting IndexedDB stats:', error);
            cacheStats.value.indexedDB = { count: 0, estimatedSize: 0, oldestEntry: null, newestEntry: null };
        }
    }
};

// Clear all route cache
const clearAllRouteCache = async () => {
    try {
        // Clear IndexedDB cache only
        if (cacheInitialized.value) {
            await clearIndexedDBCache();
            console.log('Cleared IndexedDB cache');
        }
        await updateCacheStats();
        await checkCacheStatus();
    } catch (error) {
        console.error('Error clearing cache:', error);
    }
};

// Initialize cache and cleanup on mount
onMounted(async () => {
    await initCache();
    // await clearOldCache(); // Disabled: Keep all cached data
    await updateCacheStats();
});

// Watch for selection changes to update cache status
watch([selectedDeviceIds, selectedDate, cacheInitialized], async () => {
    await checkCacheStatus();
});

// Initialize route visibility for selected devices
watch(selectedDeviceIds, (newDeviceIds) => {
    // Initialize visibility for new devices
    newDeviceIds.forEach(deviceId => {
        if (!(deviceId in routeVisibility.value)) {
            routeVisibility.value[deviceId] = true; // Default to visible
        }
    });
    
    // Remove visibility for unselected devices
    Object.keys(routeVisibility.value).forEach(deviceIdStr => {
        const deviceId = parseInt(deviceIdStr);
        if (!newDeviceIds.includes(deviceId)) {
            delete routeVisibility.value[deviceId];
        }
    });
});

// Wrapper function for fetching utilization data
const loadUtilizationData = async (days: number = 7, startDate?: string, endDate?: string) => {
    loadingUtilization.value = true;
    try {
        const data = await fetchUtilizationData(selectedDeviceIds.value, officeHourStart.value, officeHourEnd.value, days, startDate, endDate);
        utilizationData.value = data;
    } finally {
        loadingUtilization.value = false;
    }
};

// Handle custom date range selection
const loadUtilizationDataByDateRange = async (startDate: string, endDate: string) => {
    await loadUtilizationData(7, startDate, endDate); // days param ignored when date range provided
};

// Load utilization data for analytics (uses all vehicles by default)
const loadUtilizationDataForAnalytics = async (vehicleIds: number[], days: number = 7, startDate?: string, endDate?: string) => {
    loadingUtilization.value = true;
    try {
        const data = await fetchUtilizationData(vehicleIds, officeHourStart.value, officeHourEnd.value, days, startDate, endDate);
        utilizationData.value = data;
    } finally {
        loadingUtilization.value = false;
    }
};

// Watch for tab change to analytics
watch(activeTab, (newTab) => {
    if (newTab === 'analytics' && Object.keys(utilizationData.value).length === 0) {
        // Load analytics for all vehicles by default
        const allVehicleIds = devices.map(d => d.id);
        loadUtilizationDataForAnalytics(allVehicleIds);
    }
    
    // When switching to route-view, refresh the map
    if (newTab === 'route-view') {
        // Initialize visibility for new devices only (don't override existing preferences)
        selectedDeviceIds.value.forEach(deviceId => {
            if (!(deviceId in routeVisibility.value)) {
                routeVisibility.value[deviceId] = true;
            }
        });
        
        // Use a longer delay to ensure the tab content is fully rendered
        setTimeout(() => {
            console.log('Switching to route-view tab, checking map data:', {
                hasRouteViewTab: !!routeViewTabRef.value,
                hasRouteMap: !!routeViewTabRef.value?.routeMapRef,
                hasPlotFunction: !!routeViewTabRef.value?.routeMapRef?.plotRouteOnMap,
                routeDataKeys: Object.keys(routeDataCollection.value),
                selectedDevices: selectedDeviceIds.value,
                routeVisibility: { ...routeVisibility.value }
            });
            
            if (routeViewTabRef.value?.routeMapRef?.plotRouteOnMap && Object.keys(routeDataCollection.value).length > 0) {
                // Force a complete replot when switching back to route view
                routeViewTabRef.value.routeMapRef.plotRouteOnMap();
            } else if (Object.keys(routeDataCollection.value).length === 0 && selectedDeviceIds.value.length > 0 && selectedDate.value) {
                // If no route data but we have selections, try to load data
                console.log('No route data available, attempting to load...');
                loadRouteData().catch(error => {
                    console.error('Failed to load route data on tab switch:', error);
                });
            }
        }, 300);
    }
});

// Auto-load cached route data when devices or date change
watch([selectedDeviceIds, selectedDate], async ([newDeviceIds, newDate]) => {
    if (newDeviceIds.length > 0 && newDate) {
        // Clear existing data first when date changes
        routeDataCollection.value = {};
        
        // Check for cached data for the new date
        let hasAnyCachedData = false;
        
        for (const deviceId of newDeviceIds) {
            const cachedData = await getCachedData(deviceId, newDate);
            if (cachedData) {
                routeDataCollection.value[deviceId] = cachedData;
                hasAnyCachedData = true;
            }
        }
        
        if (hasAnyCachedData) {
            console.log(`Auto-loaded cached route data for ${newDeviceIds.length} vehicles on ${newDate}`);
        }
    }
}, { immediate: true });

// Initialize route history composable
const { stopPoints, allRouteHistory, getVehicleRouteHistory } = useRouteHistory(
    selectedDeviceIds,
    routeDataCollection,
    devices,
    routeAnalysisRadius
);
// Route view tab reference
const routeViewTabRef = ref(null);

// Handle stop popup from route analysis
const handleShowStopPopup = (deviceId: number, stopIndex: number) => {
    if (routeViewTabRef.value?.routeMapRef?.showStopPopup) {
        routeViewTabRef.value.routeMapRef.showStopPopup(deviceId, stopIndex);
    }
};

// Initialize with today's date
selectedDate.value = getDefaultDate();

// Initialize calendar composable
const { currentCalendarDate, calendarDays, getQuickDate } = useCalendar();

// Select date from calendar
const selectDate = (date: string) => {
    selectedDate.value = date;
};

const selectQuickDate = (type: string) => {
    selectedDate.value = getQuickDate(type);
};


// Wrapper for fetching route data
const loadRouteData = async (forceFresh = false) => {
    const data = await fetchRouteData(selectedDeviceIds.value, selectedDate.value, forceFresh);
    // Force update the collection to ensure UI refreshes
    routeDataCollection.value = { ...data };
};

const loadFreshRouteData = async () => {
    console.log('Loading fresh data - clearing existing data first');
    // Clear existing data for selected devices before fetching fresh
    selectedDeviceIds.value.forEach(deviceId => {
        delete routeDataCollection.value[deviceId];
    });
    await loadRouteData(true);
};
</script>

<template>
    <Head title="ประวัติการเดินรถรายวัน" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Configuration and Map Section -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <CalendarDays class="h-4 w-4 text-primary" />
                        <div>
                            <CardTitle class="text-lg">ประวัติการเดินรถรายวัน</CardTitle>
                            <CardDescription class="text-xs">ดูเส้นทางการเดินรถ</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'route-view'"
                            :class="[
                                activeTab === 'route-view' 
                                    ? 'border-blue-500 text-blue-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            แผนที่และเส้นทาง
                        </button>
                        <button
                            @click="activeTab = 'analytics'"
                            :class="[
                                activeTab === 'analytics' 
                                    ? 'border-blue-500 text-blue-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            วิเคราะห์ข้อมูล
                        </button>
                    </nav>
                </div>
                
                <CardContent class="pt-0">
                    <!-- Route View Tab -->
                    <RouteViewTab
                        v-if="activeTab === 'route-view'"
                        ref="routeViewTabRef"
                        v-model:selected-device-ids="selectedDeviceIds"
                        v-model:selected-date="selectedDate"
                        v-model:route-analysis-radius="routeAnalysisRadius"
                        v-model:office-hour-start="officeHourStart"
                        v-model:office-hour-end="officeHourEnd"
                        :devices="devices"
                        :vehicle-colors="vehicleColors"
                        :devices-by-type="devicesByType"
                        :has-cached-data="hasCachedData"
                        :cached-vehicle-count="cachedVehicleCount"
                        :is-loading="isLoading"
                        :cache-stats="cacheStats"
                        :cache-initialized="cacheInitialized"
                        :cache-error="cacheError"
                        :route-data-collection="routeDataCollection"
                        :route-history="allRouteHistory"
                        :route-visibility="routeVisibility"
                        :get-vehicle-color="getVehicleColor"
                        :get-vehicle-route-history="getVehicleRouteHistory"
                        @load-fresh-data="loadFreshRouteData"
                        @update-cache-stats="updateCacheStats"
                        @clear-cache="clearAllRouteCache"
                        @show-stop-popup="handleShowStopPopup"
                    />
                    
                    <!-- Analytics Tab -->
                    <AnalyticsTab
                        v-else-if="activeTab === 'analytics'"
                        :selected-device-ids="devices.map(d => d.id)"
                        :devices="devices"
                        :vehicle-colors="vehicleColors"
                        :loading-utilization="loadingUtilization"
                        :utilization-data="utilizationData"
                        @period-change="(days) => loadUtilizationDataForAnalytics(devices.map(d => d.id), days)"
                        @date-range-change="(start, end) => loadUtilizationDataForAnalytics(devices.map(d => d.id), 7, start, end)"
                    />
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>