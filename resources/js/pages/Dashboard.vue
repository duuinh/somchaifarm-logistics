<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CalendarDays, Truck } from 'lucide-vue-next';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import RouteViewTab from '@/components/Route/RouteViewTab.vue';
import AnalyticsTab from '@/components/Route/AnalyticsTab.vue';
import StopAnalysisTab from '@/components/Route/StopAnalysisTab.vue';
import RealtimeMonitoringTab from '@/components/Route/RealtimeMonitoringTab.vue';
import { useRouteAPI } from '@/composables/route/useRouteAPI';
import { useVehicleConfig } from '@/composables/route/useVehicleConfig';
import { useCalendar } from '@/composables/route/useCalendar';
import { useRouteHistory } from '@/composables/route/useRouteHistory';
import { useMultiProviderAPI } from '@/composables/route/useMultiProviderAPI';

// Initialize vehicle configuration composable
const {
    devices,
    vehicleColors,
    devicesByType,
    officeHourStart,
    officeHourEnd,
    getVehicleColor,
    getDefaultDate
} = useVehicleConfig();

// Make routeAnalysisRadius reactive
const routeAnalysisRadius = ref(1000);

// Form data
const selectedDeviceIds = ref<number[]>([]);
const selectedDate = ref('');

// API State
const routeDataCollection = ref<Record<number, any>>({});
const isLoadingRouteData = ref(false);

// Initialize Route API with devices for multi-provider support
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
} = useRouteAPI(devices);

// Tab management
const activeTab = ref('realtime');

// API Settings state
const siamGpsToken = ref(localStorage.getItem('siamgps-authorization') || '');

// Initialize multi-provider API for credential management
const { refreshCredentials } = useMultiProviderAPI(devices);

// Analytics data
const utilizationData = ref<Record<string, any>>({});
const loadingUtilization = ref(false);

// Stop efficiency time range state
const stopEfficiencyDateRange = ref<{startDate: string, endDate: string} | null>(null);
const stopEfficiencyRouteData = ref<Record<number, any>>({});

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
            } else if (Object.keys(routeDataCollection.value).length === 0 && selectedDeviceIds.value.length > 0 && selectedDate.value && !isLoadingRouteData.value) {
                // If no route data but we have selections and not already loading, try to load data
                console.log('No route data available, attempting to load...');
                loadRouteData().catch(error => {
                    console.error('Failed to load route data on tab switch:', error);
                });
            }
        }, 300);
    }
    
    // When switching to stop-efficiency, auto-select all vehicles if none selected
    if (newTab === 'stop-efficiency' && selectedDeviceIds.value.length === 0) {
        selectedDeviceIds.value = devices.map(d => d.id);
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

// Route history for stop efficiency (supports time ranges)
const stopEfficiencyRouteHistory = computed(() => {
    if (!stopEfficiencyDateRange.value) {
        // Default to current route history if no date range selected
        return allRouteHistory.value;
    }
    
    // Use the useRouteHistory composable with stop efficiency data
    const { allRouteHistory: rangeRouteHistory } = useRouteHistory(
        selectedDeviceIds,
        ref(stopEfficiencyRouteData.value),
        devices,
        routeAnalysisRadius
    );
    
    return rangeRouteHistory.value;
});

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
    if (isLoadingRouteData.value) {
        console.log('Route data loading already in progress, skipping...');
        return;
    }
    
    isLoadingRouteData.value = true;
    try {
        const data = await fetchRouteData(selectedDeviceIds.value, selectedDate.value, forceFresh);
        // Force update the collection to ensure UI refreshes
        routeDataCollection.value = { ...data };
    } finally {
        isLoadingRouteData.value = false;
    }
};

const loadFreshRouteData = async () => {
    console.log('Loading fresh data - clearing existing data first');
    // Clear existing data for selected devices before fetching fresh
    selectedDeviceIds.value.forEach(deviceId => {
        delete routeDataCollection.value[deviceId];
    });
    await loadRouteData(true);
};

// Load route data for multiple dates (for stop efficiency analysis)
const loadRouteDataForDateRange = async (startDate: string, endDate: string) => {
    if (selectedDeviceIds.value.length === 0) return;
    
    console.log(`Loading route data for date range: ${startDate} to ${endDate}`);
    stopEfficiencyRouteData.value = {};
    
    // Generate array of dates in the range
    const dates: string[] = [];
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        dates.push(d.toISOString().split('T')[0]);
    }
    
    // Load data for each date
    for (const date of dates) {
        try {
            const data = await fetchRouteData(selectedDeviceIds.value, date, false);
            // Merge data for each vehicle across dates
            Object.entries(data).forEach(([deviceId, routeData]) => {
                if (!stopEfficiencyRouteData.value[deviceId]) {
                    stopEfficiencyRouteData.value[deviceId] = { list: [] };
                }
                if (routeData?.list) {
                    stopEfficiencyRouteData.value[deviceId].list.push(...routeData.list);
                }
            });
        } catch (error) {
            console.error(`Failed to load data for ${date}:`, error);
        }
    }
    
    console.log('Completed loading route data for date range');
};

// Handle stop efficiency time range changes
const handleStopEfficiencyPeriodChange = async (days: number) => {
    const endDate = new Date();
    const startDate = new Date();
    
    if (days === 1) {
        // Today only - use current selectedDate
        stopEfficiencyDateRange.value = null; // Use default route history
        return;
    }
    
    startDate.setDate(startDate.getDate() - days + 1);
    
    const start = startDate.toISOString().split('T')[0];
    const end = endDate.toISOString().split('T')[0];
    
    stopEfficiencyDateRange.value = { startDate: start, endDate: end };
    await loadRouteDataForDateRange(start, end);
};

const handleStopEfficiencyDateRangeChange = async (startDate: string, endDate: string) => {
    stopEfficiencyDateRange.value = { startDate, endDate };
    await loadRouteDataForDateRange(startDate, endDate);
};

// Handle showing stop location on map
const handleShowStopOnMap = async (stop: any) => {
    console.log('Showing stop on map:', stop);
    console.log('Stop data:', {
        startTime: stop.startTime,
        endTime: stop.endTime,
        deviceId: stop.deviceId,
        location: stop.location,
        coordinates: { lat: stop.latitude, lng: stop.longitude }
    });
    
    // Extract date from stop data - ALWAYS extract from the stop's timestamp
    let stopDate = selectedDate.value; // Default fallback
    
    // Always try to extract date from the stop's startTime
    if (stop.startTime) {
        console.log('Raw startTime:', stop.startTime);
        
        if (stop.startTime.includes(' ')) {
            const timeParts = stop.startTime.split(' ');
            console.log('Time parts:', timeParts);
            
            if (timeParts.length > 1) {
                // Parse "HH:MM DD/MM/YYYY" format
                const dateStr = timeParts[1]; // "DD/MM/YYYY"
                if (dateStr && dateStr.includes('/')) {
                    const [day, month, year] = dateStr.split('/');
                    console.log('Date components:', { day, month, year });
                    
                    if (day && month && year) {
                        // Convert to YYYY-MM-DD format
                        stopDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
                        console.log('Extracted date from stop:', stopDate);
                    }
                }
            }
        } else {
            console.log('No space in startTime, might be time only');
        }
    } else if (stop.date) {
        // Fallback if there's a separate date field
        stopDate = stop.date;
        console.log('Using date field from stop:', stopDate);
    }
    
    console.log('Stop date to use:', stopDate, 'Current selected date:', selectedDate.value);
    
    // Ensure the device is selected
    if (!selectedDeviceIds.value.includes(stop.deviceId)) {
        selectedDeviceIds.value = [...selectedDeviceIds.value, stop.deviceId];
    } else {
        // Device already selected, just ensure we have the right ones
        selectedDeviceIds.value = [...selectedDeviceIds.value];
    }
    
    // Clear multi-day data first
    stopEfficiencyDateRange.value = null;
    stopEfficiencyRouteData.value = {};
    
    // Update selected date to match the stop's date - this should trigger data loading
    if (selectedDate.value !== stopDate) {
        console.log('Changing date from', selectedDate.value, 'to', stopDate);
        selectedDate.value = stopDate;
        // Date change will trigger the watcher to load data
    }
    
    // Switch to route-view tab
    activeTab.value = 'route-view';
    
    // Wait for date change and data loading
    await nextTick();
    
    // Give time for data to load via the watcher
    setTimeout(async () => {
        console.log('Checking route data after date change:', {
            date: selectedDate.value,
            hasData: !!routeDataCollection.value[stop.deviceId],
            dataKeys: Object.keys(routeDataCollection.value)
        });
        
        // Wait for route data to be processed and map to update
        await nextTick();
        
        // Now focus on the location after data is loaded
        setTimeout(async () => {
            if (routeViewTabRef.value?.routeMapRef) {
                const mapInstance = routeViewTabRef.value.routeMapRef.map;
                if (mapInstance) {
                    console.log('Focusing map on coordinates:', stop.latitude, stop.longitude);
                    
                    // Import Leaflet
                    const L = await import('leaflet');
                    
                    // Pan to the stop location
                    mapInstance.setView([stop.latitude, stop.longitude], 17);
                    
                    // Remove any previous highlight circle
                    if (window.highlightCircle) {
                        mapInstance.removeLayer(window.highlightCircle);
                    }
                    
                    // Add a highlight circle around the stop location
                    window.highlightCircle = L.circle([stop.latitude, stop.longitude], {
                        color: '#FFD700',
                        fillColor: '#FFD700',
                        fillOpacity: 0.2,
                        radius: 50, // 50 meter radius
                        weight: 3
                    })
                    .bindPopup(`
                        <div style="font-size: 12px;">
                            <strong>จุดจอดที่เลือก</strong><br>
                            ${stop.location}<br>
                            รถ: ${stop.vehicleName}<br>
                            เวลา: ${stop.startTime}<br>
                            ระยะเวลา: ${stop.duration} นาที<br>
                            พิกัด: ${stop.latitude.toFixed(6)}, ${stop.longitude.toFixed(6)}
                        </div>
                    `)
                    .addTo(mapInstance)
                    .openPopup();
                }
            }
        }, 500);
    }, 500);
};

// Save API credentials to localStorage
const saveApiCredentials = () => {
    // Save Andaman credentials (already handled by watchers in useRouteAPI)
    // These are automatically saved when the reactive refs change
    
    // Save Siam GPS credentials
    if (siamGpsToken.value) {
        localStorage.setItem('siamgps-authorization', siamGpsToken.value);
    } else {
        localStorage.removeItem('siamgps-authorization');
    }
    
    // Refresh multi-provider credentials
    refreshCredentials();
    
    // Show success message (you could add a toast notification here)
    console.log('API credentials saved successfully');
    alert('บันทึกการตั้งค่า API เรียบร้อยแล้ว');
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="แดชบอร์ด - ระบบติดตามการขนส่ง" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Configuration and Map Section -->
            <Card>
                <CardHeader class="py-0">
                    <div class="flex items-center gap-2">
                        <Truck class="h-4 w-4 text-primary" />
                        <div>
                            <CardTitle class="text-lg">ระบบติดตามการขนส่ง</CardTitle>
                            <CardDescription>จัดการและติดตามยานพาหนะแบบเรียลไทม์</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'realtime'"
                            :class="[
                                activeTab === 'realtime' 
                                    ? 'border-blue-500 text-blue-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            ติดตามแบบเรียลไทม์
                        </button>
                        <button
                            @click="activeTab = 'route-view'"
                            :class="[
                                activeTab === 'route-view' 
                                    ? 'border-blue-500 text-blue-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            ประวัติการเดินรถรายวัน
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
                            ภาพรวมการใช้รถ
                        </button>
                        <button
                            @click="activeTab = 'stop-efficiency'"
                            :class="[
                                activeTab === 'stop-efficiency' 
                                    ? 'border-blue-500 text-blue-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            การวิเคราะห์จุดจอด
                        </button>
                        <button
                            @click="activeTab = 'settings'"
                            :class="[
                                activeTab === 'settings' 
                                    ? 'border-blue-500 text-blue-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            ตั้งค่า API
                        </button>
                    </nav>
                </div>
                
                <CardContent class="pt-0">
                <!-- Realtime Monitoring Tab -->
                    <RealtimeMonitoringTab
                        v-if="activeTab === 'realtime'"
                        :devices="devices"
                        :vehicle-colors="vehicleColors"
                    />
                    
                    <!-- Route View Tab -->
                    <RouteViewTab
                        v-else-if="activeTab === 'route-view'"
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
                    
                    <!-- Stop Analysis Tab -->
                    <StopAnalysisTab
                        v-else-if="activeTab === 'stop-efficiency'"
                        :selected-device-ids="selectedDeviceIds"
                        :selected-date="selectedDate"
                        :devices="devices"
                        :vehicle-colors="vehicleColors"
                        :route-history="stopEfficiencyRouteHistory"
                        :route-analysis-radius="routeAnalysisRadius"
                        :get-vehicle-color="getVehicleColor"
                        @period-change="handleStopEfficiencyPeriodChange"
                        @date-range-change="handleStopEfficiencyDateRangeChange"
                        @radius-change="(radius) => routeAnalysisRadius = radius"
                    />
                    
                    <!-- API Settings Tab -->
                    <div v-else-if="activeTab === 'settings'" class="space-y-6 p-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">การตั้งค่า API Credentials</h3>
                            <p class="text-sm text-gray-600 mb-6">จัดการ API tokens สำหรับผู้ให้บริการ GPS ต่างๆ</p>
                        </div>

                        <!-- Andaman Tracking API -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Andaman Tracking API</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="andaman-auth" class="block text-sm font-medium text-gray-700 mb-1">
                                        Authorization Header
                                    </label>
                                    <input
                                        id="andaman-auth"
                                        v-model="authorizationHeader"
                                        type="password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Bearer token..."
                                    />
                                </div>
                                <div>
                                    <label for="andaman-token" class="block text-sm font-medium text-gray-700 mb-1">
                                        Token Header
                                    </label>
                                    <input
                                        id="andaman-token"
                                        v-model="tokenHeader"
                                        type="password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="API token..."
                                    />
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-xs text-gray-500">
                                    ใช้สำหรับรถ: {{ devices.filter(d => (d.provider || 'andaman') === 'andaman').map(d => d.shortname).join(', ') }}
                                </p>
                            </div>
                        </div>

                        <!-- Siam GPS Track API -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Siam GPS Track API</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label for="siamgps-auth" class="block text-sm font-medium text-gray-700 mb-1">
                                        Authorization Bearer Token
                                    </label>
                                    <input
                                        id="siamgps-auth"
                                        v-model="siamGpsToken"
                                        type="password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="your_token_here"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">
                                        ใส่เฉพาะ token (ระบบจะเพิ่ม "Bearer " ให้อัตโนมัติ)
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-xs text-gray-500">
                                    ใช้สำหรับรถ: {{ devices.filter(d => d.provider === 'siamgps').map(d => d.shortname).join(', ') }}
                                </p>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end">
                            <button
                                @click="saveApiCredentials"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                บันทึกการตั้งค่า
                            </button>
                        </div>

                        <!-- Connection Status -->
                        <div class="border-t pt-4">
                            <h4 class="font-medium text-gray-900 mb-3">สถานะการเชื่อมต่อ</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div 
                                        :class="[
                                            'w-2 h-2 rounded-full',
                                            authorizationHeader && tokenHeader ? 'bg-green-500' : 'bg-red-500'
                                        ]"
                                    ></div>
                                    <span class="text-sm">
                                        Andaman Tracking: {{ authorizationHeader && tokenHeader ? 'เชื่อมต่อแล้ว' : 'ไม่ได้เชื่อมต่อ' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div 
                                        :class="[
                                            'w-2 h-2 rounded-full',
                                            siamGpsToken ? 'bg-green-500' : 'bg-red-500'
                                        ]"
                                    ></div>
                                    <span class="text-sm">
                                        Siam GPS Track: {{ siamGpsToken ? 'เชื่อมต่อแล้ว' : 'ไม่ได้เชื่อมต่อ' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>
