<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { CalendarDays, Search, Play, Loader2, Trash2, RefreshCw, Database, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import RouteMap from '@/components/Route/RouteMap.vue';
import RouteAnalysis from '@/components/Route/RouteAnalysis.vue';
import { useRouteFiltering, deliveryLocations, pickupLocations, officeCoordinates, calculateDistance } from '@/composables/useRouteFiltering';
import { useIndexedDBCache } from '@/composables/route/useIndexedDBCache';
import { useStopAnalysis } from '@/composables/route/useStopAnalysis';

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

// Device list with IDs, names, and types
const devices = [
    { id: 26829, name: '🚛 รถ 10 ล้อไม้ดั้ม 80-2843 พัทลุง', type: '10 ล้อ', category: 'truck' },
    { id: 26852, name: '🚛 รถ 10 ล้อดั้ม 80-5757 พัทลุง', type: '10 ล้อ', category: 'truck' },
    { id: 26843, name: '🚛 รถ 10 ล้อดั้ม 80-7895 พัทลุง', type: '10 ล้อ', category: 'truck' },
    { id: 26833, name: '🚚 รถ 6 ล้อ 80-7554 พัทลุง', type: '6 ล้อ', category: 'truck' },
    { id: 26830, name: '🚛 รถพ่วง 80-7224/80-7225 พัทลุง', type: 'รถพ่วง', category: 'trailer' },
    { id: 26838, name: '🚛 รถพ่วง 80-7556/80-7558 พัทลุง', type: 'รถพ่วง', category: 'trailer' },
    { id: 46222, name: '🚛 รถพ่วง 81-1039/81-1040 พัทลุง', type: 'รถพ่วง', category: 'trailer' },
    { id: 68221, name: '🚛 รถพ่วง 81-1041/81-1042 พัทลุง', type: 'รถพ่วง', category: 'trailer' },
    { id: 46397, name: '🚚 รถกระบะ บธ-4575 พัทลุง', type: 'รถกระบะ', category: 'pickup' },
    { id: 62855, name: '🚚 รถกระบะ บธ-9515 พัทลุง', type: 'รถกระบะ', category: 'pickup' },
];

// Vehicle colors for different routes (same as RouteMap)
const vehicleColors = [
    '#FF0000', // Red
    '#0000FF', // Blue
    '#00AA00', // Green
    '#FF6600', // Orange
    '#9900FF', // Purple
    '#FF00FF', // Magenta
    '#00AAAA', // Cyan
    '#AA5500', // Brown
    '#FF3399', // Pink
    '#666666'  // Gray
];

// Helper function to get vehicle color
const getVehicleColor = (deviceId: number) => {
    const index = selectedDeviceIds.value.indexOf(deviceId);
    return index >= 0 ? vehicleColors[index % vehicleColors.length] : '#0000FF';
};

// Group devices by type for better organization
const devicesByType = computed(() => {
    const grouped = devices.reduce((acc, device) => {
        if (!acc[device.type]) {
            acc[device.type] = [];
        }
        acc[device.type].push(device);
        return acc;
    }, {} as Record<string, typeof devices>);
    
    // Define order of vehicle types
    const typeOrder = ['รถพ่วง', '10 ล้อ', '6 ล้อ', 'รถกระบะ'];
    
    const orderedGroups: Array<{ type: string; vehicles: typeof devices }> = [];
    typeOrder.forEach(type => {
        if (grouped[type]) {
            orderedGroups.push({
                type,
                vehicles: grouped[type].sort((a, b) => a.name.localeCompare(b.name))
            });
        }
    });
    
    return orderedGroups;
});

// Form data
const selectedDeviceIds = ref<number[]>([]);
const selectedDate = ref('');

// Filter settings (coordinates and pickup locations imported from composable)


// API Configuration
const authorizationHeader = ref(localStorage.getItem('route-history-auth') || '');
const tokenHeader = ref(localStorage.getItem('route-history-token') || '');

// API State
const isLoading = ref(false);
const routeDataCollection = ref<Record<number, any>>({});
const loadingStates = ref<Record<number, boolean>>({});
const apiError = ref('');

// Initialize IndexedDB cache
const {
    init: initCache,
    isInitialized: cacheInitialized,
    error: cacheError,
    getCachedData: getIndexedDBCachedData,
    setCachedData: setIndexedDBCachedData,
    clearAllCache: clearIndexedDBCache,
    getCacheStats,
    clearExpiredCache
} = useIndexedDBCache();

// Cache management (IndexedDB only)

const getCachedData = async (deviceId: number, date: string) => {
    // Use IndexedDB only
    if (cacheInitialized.value) {
        try {
            const indexedDBData = await getIndexedDBCachedData(deviceId, date);
            if (indexedDBData) {
                return indexedDBData;
            }
        } catch (error) {
            console.error('Error reading IndexedDB cache:', error);
        }
    }
    return null;
};

const setCachedData = async (deviceId: number, date: string, data: any) => {
    // Use IndexedDB only
    if (cacheInitialized.value) {
        try {
            await setIndexedDBCachedData(deviceId, date, data);
            return;
        } catch (error) {
            console.error('Error setting IndexedDB cache:', error);
        }
    } else {
        console.warn('IndexedDB cache not initialized, data not cached');
    }
};

const clearOldCache = async () => {
    // Clear expired IndexedDB cache only
    if (cacheInitialized.value) {
        try {
            const deletedCount = await clearExpiredCache();
            console.log(`Cleared ${deletedCount} expired IndexedDB cache entries`);
        } catch (error) {
            console.error('Error clearing old IndexedDB cache:', error);
        }
    }
};

// Copy status
const copied = ref(false);

// API config collapse state
const showApiConfig = ref(false);

// Calendar is always visible

// Cache status - will be updated reactively
const hasCachedDataForSelection = ref(false);
const cachedVehicleCountForSelection = ref(0);

// Check cache status for current selection
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
    await clearOldCache();
    await updateCacheStats();
});

// Watch for selection changes to update cache status
watch([selectedDeviceIds, selectedDate, cacheInitialized], async () => {
    await checkCacheStatus();
});

// No more office filtering needed

// Extract stop points from all route data
const stopPoints = computed(() => {
    const allStops: any[] = [];
    Object.values(routeDataCollection.value).forEach(routeData => {
        const dataToCheck = routeData?.list;
        if (dataToCheck) {
            const stops = dataToCheck.filter((point: any) => point.speed === 0);
            allStops.push(...stops);
        }
    });
    return allStops;
});

// Removed near-stops filter - no longer needed

// Route analysis radius control
const routeAnalysisRadius = ref(200);

// Office hours configuration
const officeHourStart = ref(8);   // 8:00 AM
const officeHourEnd = ref(17);    // 5:00 PM (17:00)

// Use shared stop analysis logic
const { analyzeRouteStops, processStops } = useStopAnalysis();

// Centralized route history calculation (calculate once, use everywhere)
const allRouteHistory = computed(() => {
    const allStops = [];
    
    selectedDeviceIds.value.forEach(deviceId => {
        const routeData = routeDataCollection.value[deviceId];
        const dataToAnalyze = routeData?.list;
        if (!dataToAnalyze || dataToAnalyze.length === 0) return;
        
        const vehicleName = devices.find(d => d.id === deviceId)?.name || `Vehicle ${deviceId}`;
        
        // Use shared stop analysis logic
        const rawStops = analyzeRouteStops(
            dataToAnalyze, 
            routeAnalysisRadius.value,
            deviceId,
            vehicleName
        );
        
        // Process and combine stops
        const processedStops = processStops(rawStops, 5);
        
        allStops.push(...processedStops);
    });
    
    // Sort all stops by start time
    return allStops.sort((a, b) => new Date(a.startTime).getTime() - new Date(b.startTime).getTime());
});

// Get route history for specific vehicle
const getVehicleRouteHistory = (deviceId: number) => {
    return allRouteHistory.value.filter(stop => stop.deviceId === deviceId);
};

// Route map reference
const routeMapRef = ref(null);

// Handle stop popup from route analysis
const handleShowStopPopup = (deviceId: number, stopIndex: number) => {
    if (routeMapRef.value && routeMapRef.value.showStopPopup) {
        routeMapRef.value.showStopPopup(deviceId, stopIndex);
    }
};

// No filtering needed - show all pickup and delivery locations

// Office marker is always visible

// Convert date to full day timestamps
const dateToFullDayTimestamps = (date: string) => {
    if (!date) return { start: 0, end: 0 };
    const startDateTime = `${date}T00:00:00`;
    const endDateTime = `${date}T23:59:59`;
    return {
        start: new Date(startDateTime).getTime(),
        end: new Date(endDateTime).getTime()
    };
};

// Generate payloads for multiple devices
const payloads = computed(() => {
    if (selectedDeviceIds.value.length === 0 || !selectedDate.value) {
        return [];
    }
    
    const timestamps = dateToFullDayTimestamps(selectedDate.value);
    return selectedDeviceIds.value.map(deviceId => ({
        deviceId,
        start: timestamps.start,
        end: timestamps.end
    }));
});

// Format JSON for display
const formattedPayloads = computed(() => {
    if (payloads.value.length === 0) return '';
    return JSON.stringify(payloads.value, null, 2);
});

// Copy to clipboard
const copyToClipboard = async () => {
    if (payloads.value.length === 0) return;
    
    try {
        await navigator.clipboard.writeText(JSON.stringify(payloads.value));
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

// Set default date (today)
const setDefaultDate = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const todayStr = `${year}-${month}-${day}`;
    
    selectedDate.value = todayStr;
};

// Initialize with today's date
setDefaultDate();

// Calendar functionality
const currentCalendarDate = ref(new Date());

// Generate calendar days
const calendarDays = computed(() => {
    const year = currentCalendarDate.value.getFullYear();
    const month = currentCalendarDate.value.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const firstDayOfWeek = firstDay.getDay(); // 0 = Sunday
    const daysInMonth = lastDay.getDate();
    
    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    
    const days = [];
    
    // Previous month's trailing days
    const prevMonth = new Date(year, month - 1, 0);
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const date = prevMonth.getDate() - i;
        const fullDate = `${year}-${String(month).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
        days.push({
            date,
            fullDate,
            isCurrentMonth: false,
            isToday: false,
            isSelected: false,
            key: `prev-${date}`
        });
    }
    
    // Current month days
    for (let date = 1; date <= daysInMonth; date++) {
        const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
        days.push({
            date,
            fullDate,
            isCurrentMonth: true,
            isToday: fullDate === todayStr,
            isSelected: fullDate === selectedDate.value,
            key: `current-${date}`
        });
    }
    
    // Next month's leading days
    const remainingDays = 42 - days.length; // 6 weeks * 7 days
    for (let date = 1; date <= remainingDays; date++) {
        const fullDate = `${year}-${String(month + 2).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
        days.push({
            date,
            fullDate,
            isCurrentMonth: false,
            isToday: false,
            isSelected: false,
            key: `next-${date}`
        });
    }
    
    return days;
});

// Select date from calendar
const selectDate = (date: string) => {
    selectedDate.value = date;
};

// Quick date selection
const selectQuickDate = (type: string) => {
    const today = new Date();
    let targetDate = new Date();
    
    switch (type) {
        case 'today':
            targetDate = today;
            break;
        case 'yesterday':
            targetDate = new Date(today);
            targetDate.setDate(today.getDate() - 1);
            break;
        case 'week':
            targetDate = new Date(today);
            targetDate.setDate(today.getDate() - 7);
            break;
    }
    
    const dateStr = `${targetDate.getFullYear()}-${String(targetDate.getMonth() + 1).padStart(2, '0')}-${String(targetDate.getDate()).padStart(2, '0')}`;
    selectedDate.value = dateStr;
    currentCalendarDate.value = targetDate;
};


// Watch for changes in headers and save to localStorage
watch(authorizationHeader, (newValue) => {
    if (newValue) {
        localStorage.setItem('route-history-auth', newValue);
    } else {
        localStorage.removeItem('route-history-auth');
    }
});

watch(tokenHeader, (newValue) => {
    if (newValue) {
        localStorage.setItem('route-history-token', newValue);
    } else {
        localStorage.removeItem('route-history-token');
    }
});

// Clear stored tokens
const clearStoredTokens = () => {
    authorizationHeader.value = '';
    tokenHeader.value = '';
    localStorage.removeItem('route-history-auth');
    localStorage.removeItem('route-history-token');
};




// Fetch route data for single vehicle
const fetchSingleVehicleData = async (deviceId: number, forceRefresh = false) => {
    if (!authorizationHeader.value || !tokenHeader.value) {
        throw new Error('กรุณากรอก Authorization และ Token');
    }

    // Check cache first (unless forced refresh)
    if (!forceRefresh) {
        const cachedData = await getCachedData(deviceId, selectedDate.value);
        if (cachedData) {
            console.log('Using cached data for vehicle', deviceId, selectedDate.value);
            return cachedData;
        }
    }

    const timestamps = dateToFullDayTimestamps(selectedDate.value);
    const payload = {
        deviceId,
        start: timestamps.start,
        end: timestamps.end
    };

    const response = await fetch('https://apitracking.andamantracking.dev/web/v1/passroute', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': authorizationHeader.value,
            'Token': tokenHeader.value,
        },
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        throw new Error(`HTTP error for vehicle ${deviceId}! status: ${response.status}`);
    }

    const data = await response.json();
    
    // Cache the successful response
    await setCachedData(deviceId, selectedDate.value, data);
    console.log('Data cached for vehicle', deviceId, selectedDate.value);
    
    return data;
};

// Fetch route data from API with caching for multiple vehicles
const fetchRouteData = async (forceRefresh = false) => {
    if (payloads.value.length === 0) {
        apiError.value = 'กรุณาเลือกรถและวันที่';
        return;
    }

    if (!authorizationHeader.value || !tokenHeader.value) {
        apiError.value = 'กรุณากรอก Authorization และ Token';
        return;
    }

    isLoading.value = true;
    apiError.value = '';
    
    // Initialize loading states for all selected vehicles
    selectedDeviceIds.value.forEach(deviceId => {
        loadingStates.value[deviceId] = true;
    });

    try {
        // Fetch data for each vehicle concurrently
        const fetchPromises = selectedDeviceIds.value.map(async (deviceId) => {
            try {
                const data = await fetchSingleVehicleData(deviceId, forceRefresh);
                routeDataCollection.value[deviceId] = data;
                loadingStates.value[deviceId] = false;
                return { deviceId, success: true, data };
            } catch (error) {
                console.error(`Error fetching data for vehicle ${deviceId}:`, error);
                loadingStates.value[deviceId] = false;
                return { deviceId, success: false, error };
            }
        });

        const results = await Promise.all(fetchPromises);
        
        // Check if any requests failed
        const failures = results.filter(result => !result.success);
        if (failures.length > 0) {
            const failedVehicles = failures.map(f => f.deviceId).join(', ');
            apiError.value = `ไม่สามารถดึงข้อมูลได้สำหรับรถ: ${failedVehicles}`;
        } else {
            apiError.value = '';
        }
        
        console.log(`Successfully loaded data for ${results.filter(r => r.success).length}/${selectedDeviceIds.value.length} vehicles`);
    } catch (error) {
        console.error('API Error:', error);
        apiError.value = error instanceof Error ? error.message : 'เกิดข้อผิดพลาดในการเรียก API';
        
        // Reset all loading states
        selectedDeviceIds.value.forEach(deviceId => {
            loadingStates.value[deviceId] = false;
        });
    } finally {
        isLoading.value = false;
    }
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
                <CardContent class="pt-0">
                    <div class="grid grid-cols-1 gap-6" :class="selectedDeviceIds.length > 0 ? 'xl:grid-cols-[280px_1fr_1fr]' : 'xl:grid-cols-[280px_1fr]'">
                        <!-- Configuration Section -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-1 gap-3">
                                <div class="md:col-span-2 xl:col-span-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <Label class="text-xs font-medium">เลือกรถ / Device ID</Label>
                                        <div class="flex gap-1">
                                            <Button
                                                @click="selectedDeviceIds = devices.map(d => d.id)"
                                                variant="ghost"
                                                size="sm"
                                                class="text-xs h-5 px-1"
                                            >
                                                ทั้งหมด
                                            </Button>
                                            <Button
                                                @click="selectedDeviceIds = []"
                                                variant="ghost"
                                                size="sm"
                                                class="text-xs h-5 px-1"
                                            >
                                                ล้าง
                                            </Button>
                                        </div>
                                    </div>
                                    <div class="border rounded-md p-2 bg-gray-50 max-h-28 overflow-y-auto">
                                        <div class="space-y-2">
                                            <div v-for="group in devicesByType" :key="group.type" class="space-y-1">
                                                <!-- Type header with select all for type -->
                                                <div class="flex items-center justify-between py-1 border-b border-gray-300">
                                                    <span class="text-xs font-semibold text-gray-700">{{ group.type }}</span>
                                                    <button
                                                        @click="() => {
                                                            const typeVehicleIds = group.vehicles.map(v => v.id);
                                                            const allSelected = typeVehicleIds.every(id => selectedDeviceIds.includes(id));
                                                            if (allSelected) {
                                                                selectedDeviceIds = selectedDeviceIds.filter(id => !typeVehicleIds.includes(id));
                                                            } else {
                                                                const newIds = [...new Set([...selectedDeviceIds, ...typeVehicleIds])];
                                                                selectedDeviceIds = newIds;
                                                            }
                                                        }"
                                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                                                    >
                                                        {{ group.vehicles.every(v => selectedDeviceIds.includes(v.id)) ? 'ยกเลิก' : 'ทั้งหมด' }}
                                                    </button>
                                                </div>
                                                <!-- Vehicles in this type -->
                                                <div class="space-y-1 pl-1">
                                                    <label 
                                                        v-for="device in group.vehicles" 
                                                        :key="device.id" 
                                                        class="flex items-center gap-1 cursor-pointer hover:bg-gray-100 p-1 rounded text-xs"
                                                    >
                                                        <input 
                                                            type="checkbox" 
                                                            :value="device.id"
                                                            v-model="selectedDeviceIds"
                                                            class="rounded border-gray-300 text-primary focus:ring-primary text-xs"
                                                        />
                                                        <div v-if="selectedDeviceIds.includes(device.id)"
                                                             class="w-2 h-2 rounded-full"
                                                             :style="{ backgroundColor: getVehicleColor(device.id) }">
                                                        </div>
                                                        <span class="truncate flex-1" :title="device.name">
                                                            {{ device.name.replace('🚛 ', '').replace('🚚 ', '') }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="selectedDeviceIds.length > 0" class="text-xs text-gray-600 mt-1">
                                        เลือกแล้ว {{ selectedDeviceIds.length }}/{{ devices.length }} คัน
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <Label class="text-xs font-medium">เลือกวันที่</Label>
                                        <div v-if="selectedDate" class="text-xs text-gray-600">
                                            {{ new Date(selectedDate + 'T00:00:00').toLocaleDateString('th-TH') }}
                                        </div>
                                    </div>
                                    
                                    <!-- Calendar Widget -->
                                    <div class="mt-2 p-3 border rounded-lg bg-gray-50">
                                        <div class="text-xs font-medium text-gray-700 mb-2">เลือกวันที่</div>
                                        <div class="grid grid-cols-7 gap-1 text-xs">
                                            <!-- Calendar Header -->
                                            <div class="text-center font-medium text-gray-500 py-1">อา</div>
                                            <div class="text-center font-medium text-gray-500 py-1">จ</div>
                                            <div class="text-center font-medium text-gray-500 py-1">อ</div>
                                            <div class="text-center font-medium text-gray-500 py-1">พ</div>
                                            <div class="text-center font-medium text-gray-500 py-1">พฤ</div>
                                            <div class="text-center font-medium text-gray-500 py-1">ศ</div>
                                            <div class="text-center font-medium text-gray-500 py-1">ส</div>
                                            
                                            <!-- Calendar Days -->
                                            <template v-for="day in calendarDays" :key="day.key">
                                                <button
                                                    v-if="day.date"
                                                    @click="selectDate(day.fullDate)"
                                                    :class="[
                                                        'text-center py-1 rounded transition-colors',
                                                        day.isSelected ? 'bg-blue-500 text-white' : 
                                                        day.isToday ? 'bg-blue-100 text-blue-800 font-medium' :
                                                        day.isCurrentMonth ? 'hover:bg-gray-200 text-gray-900' :
                                                        'text-gray-400 hover:bg-gray-100'
                                                    ]"
                                                >
                                                    {{ day.date }}
                                                </button>
                                                <div v-else class="py-1"></div>
                                            </template>
                                        </div>
                                        
                                        <!-- Quick Date Selection -->
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <div class="text-xs text-gray-600 mb-2">เลือกเร็ว:</div>
                                            <div class="flex gap-1 flex-wrap">
                                                <button
                                                    @click="selectQuickDate('today')"
                                                    class="px-2 py-1 text-xs bg-white border rounded hover:bg-gray-50"
                                                >
                                                    วันนี้
                                                </button>
                                                <button
                                                    @click="selectQuickDate('yesterday')"
                                                    class="px-2 py-1 text-xs bg-white border rounded hover:bg-gray-50"
                                                >
                                                    เมื่อวาน
                                                </button>
                                                <button
                                                    @click="selectQuickDate('week')"
                                                    class="px-2 py-1 text-xs bg-white border rounded hover:bg-gray-50"
                                                >
                                                    สัปดาห์ที่แล้ว
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <Label for="routeAnalysisRadius" class="text-xs font-medium">รัศมี (ม.)</Label>
                                    <Input
                                        id="routeAnalysisRadius"
                                        v-model.number="routeAnalysisRadius"
                                        type="number"
                                        min="50"
                                        max="1000"
                                        step="50"
                                        class="h-8 mt-1"
                                    />
                                </div>
                                
                                <div class="md:col-span-2 xl:col-span-1">
                                    <Label class="text-xs font-medium">เวลาทำงาน</Label>
                                    <div class="flex items-center gap-1 mt-1">
                                        <Input
                                            v-model.number="officeHourStart"
                                            type="number"
                                            min="0"
                                            max="23"
                                            class="h-8 w-14"
                                        />
                                        <span class="text-xs">-</span>
                                        <Input
                                            v-model.number="officeHourEnd"
                                            type="number"
                                            min="0"
                                            max="23"
                                            class="h-8 w-14"
                                        />
                                        <span class="text-xs">:00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- API Configuration -->
                            <div class="pt-2 border-t space-y-2">
                                <div class="flex items-center justify-between">
                                    <button
                                        @click="showApiConfig = !showApiConfig"
                                        class="flex items-center gap-1 text-xs font-medium hover:text-blue-600"
                                    >
                                        API Config
                                        <ChevronDown v-if="!showApiConfig" class="h-3 w-3" />
                                        <ChevronUp v-else class="h-3 w-3" />
                                    </button>
                                    <Button
                                        v-if="showApiConfig"
                                        @click="clearStoredTokens"
                                        variant="ghost"
                                        size="sm"
                                        class="text-red-600 hover:text-red-700 h-5 p-1"
                                    >
                                        <Trash2 class="h-2 w-2 mr-1" />
                                        Clear
                                    </Button>
                                </div>
                                <div v-if="showApiConfig" class="grid grid-cols-1 gap-2">
                                    <div>
                                        <Label for="authorization" class="text-xs">Authorization</Label>
                                        <Input
                                            id="authorization"
                                            v-model="authorizationHeader"
                                            type="text"
                                            placeholder="Bearer token..."
                                            class="h-8 mt-1 text-xs"
                                        />
                                    </div>
                                    <div>
                                        <Label for="token" class="text-xs">Token</Label>
                                        <Input
                                            id="token"
                                            v-model="tokenHeader"
                                            type="text"
                                            placeholder="Token value..."
                                            class="h-8 mt-1 text-xs"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 space-y-2">
                                <!-- Cache status indicator -->
                                <div v-if="hasCachedData && payloads.length > 0" class="text-xs text-green-600 bg-green-50 p-1.5 rounded border">
                                    📦 แคช {{ cachedVehicleCount }}/{{ selectedDeviceIds.length }}
                                </div>
                                
                                <div class="flex flex-col gap-2">
                                    <div class="flex gap-2">
                                        <Button 
                                            @click="() => fetchRouteData(false)"
                                            :disabled="isLoading || payloads.length === 0 || !authorizationHeader || !tokenHeader"
                                            :variant="hasCachedData ? 'outline' : 'default'"
                                            size="sm"
                                            class="h-7 flex-1"
                                        >
                                            <Loader2 v-if="isLoading" class="mr-1 h-3 w-3 animate-spin" />
                                            <Play v-else class="mr-1 h-3 w-3" />
                                            {{ isLoading ? 'โหลด...' : (hasCachedData ? 'แคช' : 'ดึงข้อมูล') }}
                                        </Button>
                                        <Button 
                                            @click="() => fetchRouteData(true)"
                                            :disabled="isLoading || payloads.length === 0 || !authorizationHeader || !tokenHeader"
                                            variant="outline"
                                            size="sm"
                                            class="h-7"
                                        >
                                            <RefreshCw class="mr-1 h-3 w-3" />
                                        </Button>
                                    </div>
                                    
                                    <!-- IndexedDB Cache Management -->
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>แคช: {{ cacheStats.indexedDB.count }} รายการ</span>
                                            <div class="flex gap-1">
                                                <Button
                                                    @click="updateCacheStats"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="text-blue-600 hover:text-blue-700 h-5 p-1 text-xs"
                                                >
                                                    <RefreshCw class="h-2 w-2" />
                                                </Button>
                                                <Button
                                                    @click="clearAllRouteCache"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="text-red-600 hover:text-red-700 h-5 p-1 text-xs"
                                                >
                                                    <Database class="h-2 w-2 mr-1" />
                                                    ล้าง
                                                </Button>
                                            </div>
                                        </div>
                                        
                                        <!-- IndexedDB Stats -->
                                        <div v-if="cacheInitialized" class="text-xs space-y-1">
                                            <div class="text-green-600 font-medium">
                                                📦 IndexedDB: {{ cacheStats.indexedDB.count }} รายการ, {{ cacheStats.indexedDB.estimatedSize }}KB
                                            </div>
                                            <div v-if="cacheStats.indexedDB.oldestEntry" class="text-gray-400">
                                                เก่าสุด: {{ new Date(cacheStats.indexedDB.oldestEntry).toLocaleDateString('th-TH') }}
                                            </div>
                                        </div>
                                        
                                        <!-- Cache Error Warning -->
                                        <div v-if="cacheError" class="text-xs text-red-600 bg-red-50 p-1 rounded">
                                            ⚠️ {{ cacheError }}
                                        </div>
                                        
                                        <!-- Cache Status -->
                                        <div v-if="!cacheInitialized" class="text-xs text-orange-600 bg-orange-50 p-1 rounded">
                                            ⚠️ กำลังเริ่มต้นแคช IndexedDB...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map Section -->
                        <div class="space-y-4">
                            <RouteMap 
                                ref="routeMapRef"
                                :route-data-collection="routeDataCollection"
                                :selected-device-ids="selectedDeviceIds"
                                :devices="devices"
                                :route-analysis-radius="routeAnalysisRadius"
                                :office-hour-start="officeHourStart"
                                :office-hour-end="officeHourEnd"
                                :route-history="allRouteHistory"
                            />
                        </div>

                        <!-- Timeline Section -->
                        <div v-if="selectedDeviceIds.length > 0" class="space-y-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="text-sm font-medium text-gray-800">ประวัติเส้นทาง</div>
                                <span class="text-xs text-gray-500">
                                    ({{ selectedDeviceIds.length }} คัน)
                                </span>
                            </div>
                            
                            <div class="space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                                <template v-for="group in devicesByType" :key="group.type">
                                    <!-- Only show group if at least one vehicle in this type is selected -->
                                    <div v-if="group.vehicles.some(v => selectedDeviceIds.includes(v.id))" class="space-y-3">
                                        <!-- Vehicle Type Header -->
                                        <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
                                            <h3 class="text-sm font-semibold text-gray-800">{{ group.type }}</h3>
                                            <span class="text-xs text-gray-500">
                                                ({{ group.vehicles.filter(v => selectedDeviceIds.includes(v.id)).length }})
                                            </span>
                                        </div>
                                        
                                        <!-- Vehicle Analysis Stack -->
                                        <div class="space-y-3">
                                            <template v-for="vehicle in group.vehicles.filter(v => selectedDeviceIds.includes(v.id))" :key="vehicle.id">
                                                <RouteAnalysis
                                                    :route-data="routeDataCollection[vehicle.id]"
                                                    :device-id="vehicle.id"
                                                    :device-name="vehicle.name"
                                                    :vehicle-type="vehicle.type"
                                                    :vehicle-color="getVehicleColor(vehicle.id)"
                                                    :route-analysis-radius="routeAnalysisRadius"
                                                    :office-hour-start="officeHourStart"
                                                    :office-hour-end="officeHourEnd"
                                                    :route-history="getVehicleRouteHistory(vehicle.id)"
                                                    @show-stop-popup="(stopIndex) => handleShowStopPopup(vehicle.id, stopIndex)"
                                                />
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>