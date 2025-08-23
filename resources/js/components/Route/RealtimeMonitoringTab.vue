<template>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">ติดตามแบบเรียลไทม์</h2>
                <p class="text-sm text-gray-600">ติดตามตำแหน่งและสถานะรถแบบเรียลไทม์</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600">อัพเดทล่าสุด: {{ lastUpdateTime }}</span>
                </div>
                
                <!-- Route History Toggle -->
                <label class="flex items-center gap-2 text-sm">
                    <input 
                        type="checkbox" 
                        v-model="showRouteHistory" 
                        @change="toggleRouteHistory"
                        class="rounded"
                    />
                    <span class="text-gray-700">แสดงเส้นทางวันนี้</span>
                </label>
                
                <button 
                    @click="refreshData"
                    :disabled="isRefreshing"
                    class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                >
                    <svg 
                        class="w-4 h-4" 
                        :class="{ 'animate-spin': isRefreshing }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    รีเฟรช
                </button>
            </div>
        </div>

        <!-- Status Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">รถทั้งหมด</div>
                <div class="text-2xl font-bold text-blue-600">{{ totalVehicles }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">กำลังวิ่ง</div>
                <div class="text-2xl font-bold text-green-600">{{ runningVehicles }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">จอดข้างนอก</div>
                <div class="text-2xl font-bold text-orange-600">{{ stoppedOutsideVehicles }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">จอดสำนักงาน</div>
                <div class="text-2xl font-bold text-purple-600">{{ stoppedAtOfficeVehicles }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm font-medium text-gray-600">ออฟไลน์</div>
                <div class="text-2xl font-bold text-gray-600">{{ offlineVehicles }}</div>
            </div>
        </div>


        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Vehicle List (Left side - 1/4) -->
            <div class="bg-white rounded-lg border shadow-sm">
                <div class="p-4 border-b">
                    <h3 class="font-medium">รายการรถ</h3>
                </div>
                <div class="max-h-[600px] overflow-y-auto">
                    <div v-for="group in filteredVehicles" :key="group.type" class="mb-2">
                        <!-- Type Header -->
                        <div class="sticky top-0 bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 border-b">
                            {{ group.type }} ({{ group.vehicles.length }} คัน)
                        </div>
                        
                        <!-- Vehicles in this type -->
                        <div 
                            v-for="vehicle in group.vehicles" 
                            :key="vehicle.id"
                            @click="selectVehicle(vehicle)"
                            :class="[
                                'p-3 border-b cursor-pointer hover:bg-gray-50 transition-colors',
                                selectedVehicle?.id === vehicle.id ? 'bg-blue-50' : ''
                            ]"
                        >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div 
                                    class="w-3 h-3 rounded-full"
                                    :style="{ backgroundColor: getVehicleColor(vehicle.id) }"
                                ></div>
                                <div>
                                    <div class="font-medium text-sm flex items-center gap-1">
                                        {{ vehicle.licensePlate }}
                                        <span 
                                            v-if="vehicle.status === 'running'"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                        >
                                            วิ่ง
                                        </span>
                                        <span 
                                            v-else-if="vehicle.status === 'stopped' && !isAtOffice(vehicle)"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                        >
                                            จอดข้างนอก
                                        </span>
                                        <span 
                                            v-else-if="vehicle.status === 'stopped' && isAtOffice(vehicle)"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                        >
                                            จอดสำนักงาน
                                        </span>
                                        <span 
                                            v-else-if="vehicle.status === 'offline'"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                        >
                                            ออฟไลน์
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium">{{ vehicle.speed }} km/h</div>
                                <div class="text-xs text-gray-500">{{ formatTime(vehicle.lastUpdate) }}</div>
                            </div>
                        </div>
                            <div class="mt-1 text-xs text-gray-600 truncate">
                                {{ vehicle.location }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map (Right side - 3/4) -->
            <div class="lg:col-span-3 bg-white rounded-lg border shadow-sm">
                <div class="p-4 border-b">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium">แผนที่ติดตาม</h3>
                        <!-- Status Color Legend -->
                        <div class="flex items-center gap-4 text-xs">
                            <span class="font-medium text-gray-600">สีสถานะ:</span>
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span>วิ่ง</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                <span>จอด</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                <span>ติดเครื่อง</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 rounded-full bg-gray-500"></div>
                                <span>ไม่ทราบ</span>
                            </div>
                            <div class="flex items-center gap-1 ml-2 border-l pl-2">
                                <div class="w-2 h-2 rounded-full bg-blue-300 border border-purple-500"></div>
                                <span>ที่สำนักงาน</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div 
                    ref="realtimeMapContainer"
                    class="w-full h-[600px]"
                ></div>
            </div>
        </div>

        <!-- Vehicle Details Panel -->
        <div v-if="selectedVehicle" class="bg-white p-6 rounded-lg border shadow-sm">
            <h3 class="text-lg font-medium mb-4">รายละเอียดรถ: {{ selectedVehicle.name }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-600">สถานะ</div>
                    <div class="font-medium flex items-center gap-1 mt-1">
                        <div 
                            class="w-2 h-2 rounded-full"
                            :class="getStatusColor(selectedVehicle.status)"
                        ></div>
                        {{ getStatusText(selectedVehicle.status) }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">ความเร็ว</div>
                    <div class="font-medium mt-1">{{ selectedVehicle.speed }} km/h</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">ระยะทางวันนี้</div>
                    <div class="font-medium mt-1">{{ selectedVehicle.todayDistance || 0 }} km</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">ตำแหน่งปัจจุบัน</div>
                    <div class="font-medium mt-1">{{ selectedVehicle.location }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">พิกัด</div>
                    <div class="font-medium mt-1">
                        {{ selectedVehicle.latitude?.toFixed(6) }}, {{ selectedVehicle.longitude?.toFixed(6) }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">อัพเดทล่าสุด</div>
                    <div class="font-medium mt-1">{{ formatTime(selectedVehicle.lastUpdate) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { officeCoordinates, pickupLocations, deliveryLocations } from '@/composables/useRouteFiltering';
import { useRouteAPI } from '@/composables/route/useRouteAPI';
import { useVehicleConfig } from '@/composables/route/useVehicleConfig';
import { useRouteHistory } from '@/composables/route/useRouteHistory';
import { useMultiProviderAPI } from '@/composables/route/useMultiProviderAPI';

interface Props {
    devices: Array<{ id: number; name: string; type: string; shortname: string; provider?: string; vehicleId?: number }>;
    vehicleColors: string[];
}

const props = defineProps<Props>();

// Get API headers and route fetching functions
const { authorizationHeader, tokenHeader, fetchRouteData } = useRouteAPI(props.devices);

// Get vehicle config for default date
const { getDefaultDate } = useVehicleConfig();

// Initialize multi-provider API
const { fetchRealtimeDataForProvider } = useMultiProviderAPI(props.devices);

// State
const isRefreshing = ref(false);
const lastUpdateTime = ref(new Date().toLocaleTimeString('th-TH'));
const selectedVehicle = ref<any>(null);
const realtimeMapContainer = ref<HTMLElement | null>(null);
const map = ref<any>(null);
const vehicleMarkers = ref<Record<number, any>>({});

// Mock realtime data (replace with actual API call)
const vehiclesData = ref<any[]>([]);
const refreshInterval = ref<NodeJS.Timeout | null>(null);

// Route history data for today
const routeHistoryData = ref<Record<number, any>>({});
const isLoadingRouteHistory = ref(false);
const showRouteHistory = ref(true);

// Route analysis radius (fixed at 200m for realtime monitoring)
const routeAnalysisRadius = ref(200);

// Initialize route history composable for stop detection
const selectedDeviceIds = ref<number[]>([]);
const { stopPoints, getVehicleRouteHistory } = useRouteHistory(
    selectedDeviceIds,
    routeHistoryData,
    props.devices,
    routeAnalysisRadius
);

// Helper function to check if vehicle is at office
const isAtOffice = (vehicle: any) => {
    if (!vehicle.latitude || !vehicle.longitude) return false;
    
    // Calculate distance from office (in meters)
    const R = 6371e3; // Earth's radius in meters
    const φ1 = officeCoordinates.lat * Math.PI/180;
    const φ2 = vehicle.latitude * Math.PI/180;
    const Δφ = (vehicle.latitude - officeCoordinates.lat) * Math.PI/180;
    const Δλ = (vehicle.longitude - officeCoordinates.lng) * Math.PI/180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;
    
    return distance <= 200; // Within 200 meters of office
};

// Computed
const totalVehicles = computed(() => vehiclesData.value.length);
const runningVehicles = computed(() => vehiclesData.value.filter(v => v.status === 'running').length);
const stoppedOutsideVehicles = computed(() => vehiclesData.value.filter(v => v.status === 'stopped' && !isAtOffice(v)).length);
const stoppedAtOfficeVehicles = computed(() => vehiclesData.value.filter(v => v.status === 'stopped' && isAtOffice(v)).length);
const offlineVehicles = computed(() => vehiclesData.value.filter(v => v.status === 'offline').length);

const filteredVehicles = computed(() => {
    // Group vehicles by type
    const grouped = vehiclesData.value.reduce((acc, vehicle) => {
        const deviceInfo = props.devices.find(d => d.id === vehicle.id);
        const vehicleType = deviceInfo?.type || 'อื่นๆ';
        
        if (!acc[vehicleType]) {
            acc[vehicleType] = [];
        }
        acc[vehicleType].push(vehicle);
        return acc;
    }, {} as Record<string, any[]>);
    
    // Define type order and return as array of groups
    const typeOrder = ['รถกระบะ', 'รถพ่วง', '10 ล้อ', '6 ล้อ', 'อื่นๆ'];
    
    return typeOrder.map(type => ({
        type,
        vehicles: grouped[type] || []
    })).filter(group => group.vehicles.length > 0);
});

// Methods
const getStatusColor = (status: string) => {
    switch(status) {
        case 'running': return 'bg-green-500';
        case 'stopped': return 'bg-orange-500';
        case 'offline': return 'bg-gray-500';
        default: return 'bg-gray-300';
    }
};

const getStatusText = (status: string) => {
    switch(status) {
        case 'running': return 'กำลังวิ่ง';
        case 'stopped': return 'จอด';
        case 'offline': return 'ออฟไลน์';
        default: return 'ไม่ทราบ';
    }
};

const formatTime = (timestamp: Date | string) => {
    if (!timestamp) return '-';
    const date = new Date(timestamp);
    return date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
};

// Get vehicle color based on device position in the devices array
const getVehicleColor = (vehicleId: number) => {
    const deviceIndex = props.devices.findIndex(d => d.id === vehicleId);
    return deviceIndex >= 0 ? props.vehicleColors[deviceIndex % props.vehicleColors.length] : props.vehicleColors[0];
};


const selectVehicle = (vehicle: any) => {
    selectedVehicle.value = vehicle;
    // Center map on selected vehicle
    if (map.value && vehicle.latitude && vehicle.longitude) {
        map.value.setView([vehicle.latitude, vehicle.longitude], 15);
    }
};

// Fetch real-time data from multiple API providers
const fetchRealtimeData = async () => {
    try {
        console.log('Fetching realtime data from multiple providers...');
        
        // Group devices by provider
        const devicesByProvider = props.devices.reduce((acc, device) => {
            const provider = device.provider || 'andaman';
            if (!acc[provider]) {
                acc[provider] = [];
            }
            acc[provider].push(device.id);
            return acc;
        }, {} as Record<string, number[]>);
        
        console.log('Devices by provider:', devicesByProvider);
        
        // Fetch data from each provider
        const allVehicleData: any[] = [];
        
        for (const [provider, deviceIds] of Object.entries(devicesByProvider)) {
            try {
                console.log(`Fetching from ${provider} for devices:`, deviceIds);
                const providerData = await fetchRealtimeDataForProvider(provider, deviceIds);
                
                if (provider === 'andaman') {
                    // Transform Andaman data to our format
                    const transformedData = providerData.map((vehicle: any) => {
                        const deviceConfig = props.devices.find(d => d.id === vehicle.device_id);
                        
                        let status = 'offline';
                        if (vehicle.online === 1) {
                            if (vehicle.status_name === 'Stop') {
                                status = 'stopped';
                            } else if (parseFloat(vehicle.speed) > 0) {
                                status = 'running';
                            } else {
                                status = 'stopped';
                            }
                        }
                        
                        return {
                            id: vehicle.device_id,
                            name: deviceConfig?.name || vehicle.name,
                            licensePlate: deviceConfig?.shortname || vehicle.name,
                            driver: vehicle.employee || vehicle.employee2 || '-',
                            status: status,
                            speed: Math.round(parseFloat(vehicle.speed) || 0),
                            latitude: parseFloat(vehicle.latitude),
                            longitude: parseFloat(vehicle.longitude),
                            location: vehicle.address || 'ไม่ระบุ',
                            lastUpdate: new Date(vehicle.event_stamp),
                            todayDistance: Math.round(vehicle.odoToday || 0),
                            fuel: vehicle.fuel_liters !== '-' ? `${vehicle.fuel_liters}L` : '-',
                            ignition: vehicle.ignition,
                            satellites: vehicle.satellites,
                            signalStrength: vehicle.fld_signalStrength,
                            provider: provider
                        };
                    });
                    allVehicleData.push(...transformedData);
                } else if (provider === 'siamgps') {
                    // Transform Siam GPS data (assuming it's already transformed by the provider)
                    const transformedData = providerData.map((vehicle: any) => {
                        const deviceConfig = props.devices.find(d => d.id === vehicle.device_id);
                        
                        let status = 'offline';
                        if (vehicle.online === 1) {
                            if (parseFloat(vehicle.speed) > 0) {
                                status = 'running';
                            } else {
                                status = 'stopped';
                            }
                        }
                        
                        return {
                            id: vehicle.device_id,
                            name: deviceConfig?.name || vehicle.name,
                            licensePlate: deviceConfig?.shortname || vehicle.name,
                            driver: '-',
                            status: status,
                            speed: Math.round(parseFloat(vehicle.speed) || 0),
                            latitude: parseFloat(vehicle.latitude),
                            longitude: parseFloat(vehicle.longitude),
                            location: vehicle.address || 'ไม่ระบุ',
                            lastUpdate: new Date(vehicle.event_stamp),
                            todayDistance: 0,
                            fuel: vehicle.fuel_liters !== '-' ? `${vehicle.fuel_liters}L` : '-',
                            ignition: vehicle.ignition,
                            satellites: vehicle.satellites,
                            signalStrength: 0,
                            provider: provider
                        };
                    });
                    allVehicleData.push(...transformedData);
                }
            } catch (error) {
                console.error(`Error fetching data from ${provider}:`, error);
                // Continue with other providers even if one fails
            }
        }
        
        console.log('Combined vehicle data:', allVehicleData);
        return allVehicleData;
    } catch (error) {
        console.error('Error fetching realtime data:', error);
        return [];
    }
};

// Load today's route history for all vehicles
const loadTodayRouteHistory = async () => {
    if (isLoadingRouteHistory.value) return;
    
    isLoadingRouteHistory.value = true;
    try {
        const today = getDefaultDate();
        const allDeviceIds = props.devices.map(d => d.id);
        console.log('Loading route history for today:', today, 'devices:', allDeviceIds);
        
        const routeData = await fetchRouteData(allDeviceIds, today, false);
        routeHistoryData.value = routeData;
        
        // Update selected device IDs for route history composable
        selectedDeviceIds.value = allDeviceIds;
        
        // Update map with route history
        await updateMapWithRouteHistory();
        
        // Auto-fit map after loading route history
        await autoFitMap();
    } catch (error) {
        console.error('Error loading route history:', error);
    } finally {
        isLoadingRouteHistory.value = false;
    }
};

// Toggle route history visibility
const toggleRouteHistory = async () => {
    if (!map.value) return;
    
    if (showRouteHistory.value) {
        // Show route history
        if (Object.keys(routeHistoryData.value).length === 0) {
            // Load route history if not already loaded
            await loadTodayRouteHistory();
        } else {
            // Just show existing polylines
            await updateMapWithRouteHistory();
        }
        
        // Auto-fit after toggling route history
        await autoFitMap();
    } else {
        // Hide route history and stop markers
        const L = await import('leaflet');
        Object.values(routePolylines.value).forEach(polyline => {
            map.value.removeLayer(polyline);
        });
        Object.values(stopMarkers.value).forEach(markerArray => {
            markerArray.forEach(marker => map.value.removeLayer(marker));
        });
        routePolylines.value = {};
        stopMarkers.value = {};
        
        // Auto-fit after hiding route history
        await autoFitMap();
    }
};

// Append new vehicle positions to route history
const appendToRouteHistory = async (newVehicleData: any[]) => {
    if (!showRouteHistory.value || newVehicleData.length === 0) return;
    
    newVehicleData.forEach(vehicle => {
        if (!vehicle.latitude || !vehicle.longitude) return;
        
        // Create a new GPS point in the same format as the API
        const newPoint = {
            latitude: vehicle.latitude.toString(),
            longitude: vehicle.longitude.toString(),
            speed: vehicle.speed || 0,
            event_stamp: vehicle.lastUpdate || new Date().toISOString(),
            direction: vehicle.direction || 0,
            satellites: vehicle.satellites || 0,
            ignition: vehicle.ignition || 0
        };
        
        // Initialize route data for this vehicle if it doesn't exist
        if (!routeHistoryData.value[vehicle.id]) {
            routeHistoryData.value[vehicle.id] = { list: [] };
        }
        
        // Get the existing route data
        const existingData = routeHistoryData.value[vehicle.id];
        if (!existingData.list) {
            existingData.list = [];
        }
        
        // Check if this is a new position (avoid duplicates)
        const lastPoint = existingData.list[existingData.list.length - 1];
        const isDuplicate = lastPoint && 
            Math.abs(parseFloat(lastPoint.latitude) - vehicle.latitude) < 0.00001 &&
            Math.abs(parseFloat(lastPoint.longitude) - vehicle.longitude) < 0.00001;
        
        if (!isDuplicate) {
            // Append the new point
            existingData.list.push(newPoint);
            console.log(`Appended new position for vehicle ${vehicle.id}:`, newPoint);
        }
    });
    
    // Update route history display if visible
    if (showRouteHistory.value) {
        await updateMapWithRouteHistory();
    }
};

const refreshData = async () => {
    isRefreshing.value = true;
    
    try {
        const newVehicleData = await fetchRealtimeData();
        vehiclesData.value = newVehicleData;
        lastUpdateTime.value = new Date().toLocaleTimeString('th-TH');
        
        // Append new positions to route history
        await appendToRouteHistory(newVehicleData);
        
        await updateMapMarkers();
    } catch (error) {
        console.error('Error refreshing data:', error);
    } finally {
        isRefreshing.value = false;
    }
};

// Initialize map
const initializeRealtimeMap = async () => {
    if (!realtimeMapContainer.value) return;
    
    const L = await import('leaflet');
    
    // Initialize map
    map.value = L.map(realtimeMapContainer.value, {
        center: [officeCoordinates.lat, officeCoordinates.lng],
        zoom: 10
    });
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map.value);
    
};

// Route history polylines and stop markers
const routePolylines = ref<Record<number, any>>({});
const stopMarkers = ref<Record<number, any[]>>({});

// Update map with route history
const updateMapWithRouteHistory = async () => {
    if (!map.value) return;
    
    const L = await import('leaflet');
    
    // Clear existing route polylines and stop markers
    Object.values(routePolylines.value).forEach(polyline => {
        map.value.removeLayer(polyline);
    });
    Object.values(stopMarkers.value).forEach(markerArray => {
        markerArray.forEach(marker => map.value.removeLayer(marker));
    });
    routePolylines.value = {};
    stopMarkers.value = {};
    
    // Add route history for each vehicle
    Object.entries(routeHistoryData.value).forEach(([deviceIdStr, routeData]) => {
        const deviceId = parseInt(deviceIdStr);
        if (!routeData?.list || routeData.list.length === 0) return;
        
        // Get vehicle color based on device ID position in devices array
        const deviceIndex = props.devices.findIndex(d => d.id === deviceId);
        const color = deviceIndex >= 0 ? props.vehicleColors[deviceIndex % props.vehicleColors.length] : props.vehicleColors[0];
        
        // Create polyline from route points
        const routePoints = routeData.list.map((point: any) => [
            parseFloat(point.latitude),
            parseFloat(point.longitude)
        ]);
        
        if (routePoints.length > 1 && showRouteHistory.value) {
            const polyline = L.polyline(routePoints, {
                color: color,
                weight: 3,
                opacity: 0.7
            }).addTo(map.value);
            
            routePolylines.value[deviceId] = polyline;
        }
        
        // Add stop markers for this vehicle
        if (showRouteHistory.value) {
            const vehicleStops = getVehicleRouteHistory(deviceId);
            if (vehicleStops && vehicleStops.length > 0) {
                stopMarkers.value[deviceId] = [];
                
                vehicleStops.forEach((stop: any, stopIndex: number) => {
                    // Create stop marker
                    const stopIcon = L.divIcon({
                        html: `<div style="
                            width: 16px; height: 16px; border-radius: 50%; 
                            background-color: white; 
                            border: 2px solid ${color}; 
                            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                            display: flex; align-items: center; justify-content: center;
                            font-size: 10px; font-weight: bold; color: ${color};
                        ">${stopIndex + 1}</div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8],
                        popupAnchor: [0, -8],
                        className: 'stop-marker'
                    });
                    
                    const stopMarker = L.marker([stop.latitude, stop.longitude], { 
                        icon: stopIcon
                    })
                    .bindPopup(`
                        <div style="font-size: 12px;">
                            <strong>จุดหยุด #${stopIndex + 1}</strong><br>
                            รถ: ${props.devices.find(d => d.id === deviceId)?.shortname || 'ไม่ระบุ'}<br>
                            เวลาเริ่ม: ${stop.startTime}<br>
                            เวลาสิ้นสุด: ${stop.endTime}<br>
                            ระยะเวลา: ${stop.duration} นาที<br>
                            ตำแหน่ง: ${stop.location}<br>
                            พิกัด: ${stop.latitude.toFixed(6)}, ${stop.longitude.toFixed(6)}
                        </div>
                    `)
                    .addTo(map.value);
                    
                    stopMarkers.value[deviceId].push(stopMarker);
                });
            }
        }
    });
    
    // Auto-fit map after updating route history
    await autoFitMap();
};

// Update vehicle markers on map
const updateMapMarkers = async () => {
    if (!map.value) return;
    
    const L = await import('leaflet');
    
    // Clear existing vehicle markers
    Object.values(vehicleMarkers.value).forEach(marker => {
        map.value.removeLayer(marker);
    });
    vehicleMarkers.value = {};
    
    // Group vehicles by location (within 100 meters of each other)
    const locationGroups: Record<string, any[]> = {};
    
    vehiclesData.value.forEach(vehicle => {
        if (!vehicle.latitude || !vehicle.longitude) return;
        
        // Find if this vehicle is close to any existing group
        let groupKey = null;
        const currentLat = vehicle.latitude;
        const currentLng = vehicle.longitude;
        
        for (const [key, group] of Object.entries(locationGroups)) {
            const [groupLat, groupLng] = key.split(',').map(Number);
            
            // Calculate distance in meters
            const R = 6371e3;
            const φ1 = groupLat * Math.PI/180;
            const φ2 = currentLat * Math.PI/180;
            const Δφ = (currentLat - groupLat) * Math.PI/180;
            const Δλ = (currentLng - groupLng) * Math.PI/180;
            
            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c;
            
            if (distance <= 100) { // Within 100 meters
                groupKey = key;
                break;
            }
        }
        
        if (groupKey) {
            locationGroups[groupKey].push(vehicle);
        } else {
            // Create new group
            const newKey = `${currentLat},${currentLng}`;
            locationGroups[newKey] = [vehicle];
        }
    });
    
    // Create markers for each location group
    Object.entries(locationGroups).forEach(([locationKey, vehicles]) => {
        const [lat, lng] = locationKey.split(',').map(Number);
        const vehicleCount = vehicles.length;
        
        if (vehicleCount === 1) {
            // Single vehicle - use original styling
            const vehicle = vehicles[0];
            const deviceInfo = props.devices.find(d => d.id === vehicle.id);
            const vehicleType = deviceInfo?.type || 'unknown';
            const atOffice = isAtOffice(vehicle);
            
            // Color based on vehicle status
            const getStatusColor = (vehicle: any) => {
                // Check different status field names and normalize to lowercase
                const status = (vehicle.status_name || vehicle.vehicleStatus || vehicle.status || '').toLowerCase();
                
                // Debug log to see what status we're getting
                console.log('Vehicle status:', vehicle.id, status, { 
                    status_name: vehicle.status_name, 
                    vehicleStatus: vehicle.vehicleStatus, 
                    status: vehicle.status,
                    speed: vehicle.speed 
                });
                
                // Check for moving/running status
                if (status === 'running' || status === 'moving' || vehicle.speed > 0) {
                    return '#10B981'; // Green for moving
                } else if (status === 'stopped' || status === 'stop' || status === 'park') {
                    return '#EF4444'; // Red for stopped
                } else if (status === 'idle') {
                    return '#F59E0B'; // Amber for idle
                } else if (status === 'offline') {
                    return '#DC2626'; // Dark red for offline
                } else {
                    return '#6B7280'; // Gray for unknown
                }
            };
            
            const markerColor = getStatusColor(vehicle);
            const borderColor = atOffice ? '#A855F7' : 'white';
            const borderWidth = atOffice ? '3px' : '2px';
            
            const vehicleIcon = L.divIcon({
                html: `<div style="width: 14px; height: 14px; border-radius: 50%; 
                            background-color: ${markerColor}; 
                            border: ${borderWidth} solid ${borderColor}; 
                            box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7],
                popupAnchor: [0, -7],
                className: 'vehicle-dot'
            });
            
            const marker = L.marker([lat, lng], { 
                icon: vehicleIcon,
                title: vehicle.licensePlate
            });
            
            // Only show tooltip for running vehicles or vehicles outside office
            // Get the actual vehicle data to check status
            const vehicleData = vehiclesData.value.find(v => v.id === vehicle.id);
            const isRunning = vehicleData?.status === 'running';
            const isOutsideOffice = !atOffice;
            
            if (isRunning || isOutsideOffice) {
                marker.bindTooltip(`
                    <div style="font-size: 12px; font-weight: 600; color: #374151; padding: 2px 0;">
                        ${vehicle.licensePlate}
                    </div>
                `, {
                    permanent: true,
                    direction: 'top',
                    offset: [0, -15],
                    className: 'vehicle-tooltip'
                });
            }
            
            marker.addTo(map.value);
            
            vehicleMarkers.value[vehicle.id] = marker;
        } else {
            // Multiple vehicles - create cluster marker with count
            const hasOfficeVehicle = vehicles.some(v => isAtOffice(v));
            const borderColor = hasOfficeVehicle ? '#A855F7' : 'white';
            const borderWidth = hasOfficeVehicle ? '3px' : '2px';
            
            const clusterIcon = L.divIcon({
                html: `<div style="
                    width: 32px; height: 32px; border-radius: 8px; 
                    background: rgba(102, 126, 234, 0.3);
                    border: 2px solid rgba(255, 255, 255, 0.8); 
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                    display: flex; align-items: center; justify-content: center;
                    color: #374151; font-weight: bold; font-size: 14px;
                    position: relative; z-index: 1000;
                    backdrop-filter: blur(4px);
                ">${vehicleCount}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -16],
                className: 'vehicle-cluster'
            });
            
            const marker = L.marker([lat, lng], { 
                icon: clusterIcon,
                title: `${vehicleCount} คัน`
            })
            .addTo(map.value);
            
            // Store marker for the first vehicle (for cleanup)
            vehicleMarkers.value[vehicles[0].id] = marker;
        }
    });
    
    // Auto-fit map to show all vehicles
    await autoFitMap();
};

// Auto-fit map to show all vehicles and route history
const autoFitMap = async () => {
    if (!map.value || vehiclesData.value.length === 0) return;
    
    const L = await import('leaflet');
    const bounds = L.latLngBounds([]);
    
    // Add current vehicle positions to bounds
    vehiclesData.value.forEach(vehicle => {
        if (vehicle.latitude && vehicle.longitude) {
            bounds.extend([vehicle.latitude, vehicle.longitude]);
        }
    });
    
    // Add route history points to bounds if shown
    if (showRouteHistory.value && Object.keys(routeHistoryData.value).length > 0) {
        Object.values(routeHistoryData.value).forEach((routeData: any) => {
            if (routeData?.list) {
                routeData.list.forEach((point: any) => {
                    bounds.extend([parseFloat(point.latitude), parseFloat(point.longitude)]);
                });
            }
        });
    }
    
    // Always include office in bounds
    bounds.extend([officeCoordinates.lat, officeCoordinates.lng]);
    
    // Fit map to bounds with padding
    if (bounds.isValid()) {
        map.value.fitBounds(bounds, {
            padding: [20, 20]
        });
    }
};

// Lifecycle
onMounted(async () => {
    await initializeRealtimeMap();
    await loadTodayRouteHistory(); // Load route history first
    await refreshData(); // Then load current positions
    
    // Set up auto-refresh every 30 seconds (only for realtime data, not route history)
    refreshInterval.value = setInterval(() => {
        refreshData();
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
    if (map.value) {
        // Clear all markers and polylines
        Object.values(vehicleMarkers.value).forEach(marker => {
            map.value.removeLayer(marker);
        });
        Object.values(routePolylines.value).forEach(polyline => {
            map.value.removeLayer(polyline);
        });
        Object.values(stopMarkers.value).forEach(markerArray => {
            markerArray.forEach(marker => map.value.removeLayer(marker));
        });
        map.value.remove();
    }
});
</script>

<style scoped>
:deep(.vehicle-dot) {
    background: transparent !important;
    border: none !important;
}

:deep(.vehicle-cluster) {
    background: transparent !important;
    border: none !important;
    z-index: 1000 !important;
    position: relative !important;
}

:deep(.stop-marker) {
    background: transparent !important;
    border: none !important;
}

:deep(.vehicle-tooltip) {
    background: rgba(255, 255, 255, 0.95) !important;
    border: 1px solid #E5E7EB !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    backdrop-filter: blur(8px);
}

:deep(.vehicle-tooltip::before) {
    border-top-color: rgba(255, 255, 255, 0.95) !important;
}

:deep(.leaflet-tooltip-top:before) {
    border-top-color: rgba(255, 255, 255, 0.95) !important;
}
</style>