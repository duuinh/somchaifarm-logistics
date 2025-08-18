<template>
    <div class="space-y-4">
        <!-- Map Section -->
        <div class="border rounded-lg overflow-hidden">
            <div 
                id="mapContainer"
                ref="mapContainer"
                class="w-full h-[500px]"
                style="min-height: 500px;"
            ></div>
            <div class="p-3 bg-gray-50 text-xs text-gray-600">
                <!-- Map Controls -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-medium text-gray-700">แสดง/ซ่อน จุดต่างๆ</div>
                        <button
                            @click="focusOnRoute"
                            class="flex items-center gap-1 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="Object.keys(props.routeDataCollection).length === 0"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            โฟกัสเส้นทาง
                        </button>
                    </div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="showOffice" 
                                class="rounded border-gray-300 text-primary focus:ring-primary text-xs"
                            />
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span>สำนักงาน</span>
                        </label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="showPickupPoints" 
                                class="rounded border-gray-300 text-primary focus:ring-primary text-xs"
                            />
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span>จุดรับสินค้า</span>
                        </label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="showDeliveryPoints" 
                                class="rounded border-gray-300 text-primary focus:ring-primary text-xs"
                            />
                            <div class="w-3 h-3 bg-green-600 rounded-full"></div>
                            <span>จุดส่งสินค้า</span>
                        </label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="showStopPins" 
                                class="rounded border-gray-300 text-primary focus:ring-primary text-xs"
                            />
                            <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                            <span>จุดจอด</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue';
import 'leaflet/dist/leaflet.css';
import { officeCoordinates, pickupLocations, deliveryLocations, calculateDistance } from '@/composables/useRouteFiltering';
import { useStopAnalysis } from '@/composables/route/useStopAnalysis';

interface Props {
    routeDataCollection: Record<number, any>;
    selectedDeviceIds: number[];
    devices: Array<{ id: number; name: string }>;
    routeAnalysisRadius: number;
    officeHourStart: number;
    officeHourEnd: number;
    routeHistory?: any[]; // Pre-calculated route history
    routeVisibility?: Record<number, boolean>; // Route visibility controls
}

const props = defineProps<Props>();

// Use shared stop analysis logic
const { analyzeRouteStops, processStops } = useStopAnalysis();

// Constants imported directly

// Map related
const mapContainer = ref<HTMLElement | null>(null);
const map = ref<any>(null);
let routeMarkers: any[] = [];
let routePolylines: Record<number, any> = {};

// Vehicle colors for different routes
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

// Marker visibility controls (office enabled by default, pickup/delivery hidden)
const showOffice = ref(true);
const showPickupPoints = ref(false);
const showDeliveryPoints = ref(false);
const showStopPins = ref(true);

// Store marker references for show/hide functionality
let officeMarker: any = null;
let pickupMarkers: any[] = [];
let deliveryMarkers: any[] = [];
let stopMarkers: any[] = [];

// Use route visibility from parent component
const routeVisibility = computed(() => props.routeVisibility || {});

// Watch for route visibility changes and update map
watch(() => props.routeVisibility, (newVisibility) => {
    if (!newVisibility || !map.value) return;
    
    Object.entries(newVisibility).forEach(([deviceIdStr, isVisible]) => {
        const deviceId = parseInt(deviceIdStr);
        const polyline = routePolylines[deviceId];
        
        if (polyline) {
            if (isVisible && !map.value.hasLayer(polyline)) {
                polyline.addTo(map.value);
            } else if (!isVisible && map.value.hasLayer(polyline)) {
                map.value.removeLayer(polyline);
            }
        }
    });
}, { deep: true });


// Helper functions now use filtered data from props
const shouldShowOfficeMarker = () => {
    // Office marker only depends on its own checkbox - not affected by stop filtering
    return showOffice.value;
};

// Get route history from RouteAnalysis component 
const routeAnalysisComponent = ref(null);

// Use pre-calculated route history or fallback to local calculation
const routeHistory = computed(() => {
    // Use pre-calculated data if available
    if (props.routeHistory && props.routeHistory.length > 0) {
        return props.routeHistory;
    }
    
    // Fallback to local calculation for backward compatibility
    const allStops = [];
    
    props.selectedDeviceIds.forEach(deviceId => {
        const routeData = props.routeDataCollection[deviceId];
        const dataToAnalyze = routeData?.list;
        if (!dataToAnalyze || dataToAnalyze.length === 0) return;
        
        const vehicleName = props.devices.find(d => d.id === deviceId)?.name || `Vehicle ${deviceId}`;
        
        const rawStops = analyzeRouteStops(
            dataToAnalyze, 
            props.routeAnalysisRadius,
            deviceId,
            vehicleName
        );
        
        const processedStops = processStops(rawStops, 5);
        allStops.push(...processedStops);
    });
    
    return allStops.sort((a, b) => new Date(a.startTime).getTime() - new Date(b.startTime).getTime());
});

// Function to trigger popup for a specific stop
const showStopPopup = (deviceId: number, stopIndex: number) => {
    // Find the stop in routeHistory that matches the deviceId and index
    const deviceStops = routeHistory.value.filter(stop => stop.deviceId === deviceId);
    if (deviceStops[stopIndex]) {
        const targetStop = deviceStops[stopIndex];
        
        // Find the marker that matches this stop's coordinates
        const matchingMarker = stopMarkers.find(marker => {
            const latLng = marker.getLatLng();
            return Math.abs(latLng.lat - targetStop.latitude) < 0.0001 && 
                   Math.abs(latLng.lng - targetStop.longitude) < 0.0001;
        });
        
        if (matchingMarker) {
            matchingMarker.openPopup();
            // Center map on the marker
            if (map.value) {
                map.value.setView(matchingMarker.getLatLng(), Math.max(map.value.getZoom(), 15));
            }
        }
    }
};

// Function to focus map on all routes
const focusOnRoute = async () => {
    if (!map.value) {
        console.log('Map not available for focusing');
        return;
    }
    
    if (Object.keys(props.routeDataCollection).length === 0) {
        console.log('No route data available for focusing');
        return;
    }
    
    const L = await import('leaflet');
    
    try {
        // Get all route coordinates from all vehicles
        const allRouteCoordinates: number[][] = [];
        
        Object.values(props.routeDataCollection).forEach(routeData => {
            if (routeData?.list && routeData.list.length > 0) {
                const coordinates = routeData.list.map((point: any) => [point.latitude, point.longitude]);
                allRouteCoordinates.push(...coordinates);
            }
        });
        
        console.log('Manual focus: found', allRouteCoordinates.length, 'route coordinates');
        
        if (allRouteCoordinates.length > 0) {
            // Create a bounds object from all route points
            const bounds = L.latLngBounds(allRouteCoordinates);
            
            if (bounds.isValid()) {
                console.log('Manually focusing map on routes');
                // Fit map to show all route points with some padding
                map.value.fitBounds(bounds, {
                    padding: [40, 40],
                    animate: true,
                    duration: 1.0,
                    maxZoom: 16 // Prevent zooming in too much
                });
            } else {
                console.log('Invalid bounds for focusing');
            }
        } else {
            console.log('No route coordinates found for focusing');
        }
    } catch (error) {
        console.error('Error focusing on route:', error);
    }
};

const shouldShowPickupMarker = (index: number) => {
    return showPickupPoints.value;
};

const shouldShowDeliveryMarker = (index: number) => {
    return showDeliveryPoints.value;
};

// Using only popups and title attributes - no tooltips to avoid positioning errors

// Function to update marker visibility
const updateMarkersBasedOnCheckboxes = () => {
    if (!map.value) return;
    
    try {
        // Update office marker
        if (officeMarker) {
            const shouldShow = shouldShowOfficeMarker();
            if (shouldShow && !map.value.hasLayer(officeMarker)) {
                officeMarker.addTo(map.value);
            } else if (!shouldShow && map.value.hasLayer(officeMarker)) {
                map.value.removeLayer(officeMarker);
            }
        }
        
        // Update pickup markers
        pickupMarkers.forEach((marker, index) => {
            const shouldShow = shouldShowPickupMarker(index);
            if (shouldShow && !map.value.hasLayer(marker)) {
                marker.addTo(map.value);
            } else if (!shouldShow && map.value.hasLayer(marker)) {
                map.value.removeLayer(marker);
            }
        });
        
        // Update delivery markers
        deliveryMarkers.forEach((marker, index) => {
            const shouldShow = shouldShowDeliveryMarker(index);
            if (shouldShow && !map.value.hasLayer(marker)) {
                marker.addTo(map.value);
            } else if (!shouldShow && map.value.hasLayer(marker)) {
                map.value.removeLayer(marker);
            }
        });
        
        // Update stop markers
        stopMarkers.forEach(marker => {
            if (showStopPins.value && !map.value.hasLayer(marker)) {
                marker.addTo(map.value);
            } else if (!showStopPins.value && map.value.hasLayer(marker)) {
                map.value.removeLayer(marker);
            }
        });
    } catch (error) {
        console.error('Error updating markers:', error);
    }
};



// Initialize map
const initializeMap = async () => {
    console.log('Initializing map...');
    
    // If map already exists, clean it up first
    if (map.value) {
        console.log('Map already exists, cleaning up...');
        map.value.remove();
        map.value = null;
    }
    
    let container = mapContainer.value;
    if (!container) {
        console.log('Ref not found, trying getElementById...');
        container = document.getElementById('mapContainer') as HTMLElement;
        if (container) {
            mapContainer.value = container;
        }
    }
    
    console.log('Map container:', container);
    if (!container) {
        console.error('Map container not found - cannot initialize map');
        return;
    }

    const rect = container.getBoundingClientRect();
    console.log('Container dimensions:', rect.width, 'x', rect.height);
    if (rect.width === 0 || rect.height === 0) {
        console.error('Container has no dimensions, retrying in 200ms...');
        setTimeout(initializeMap, 200);
        return;
    }

    console.log('Loading Leaflet...');
    const L = await import('leaflet');
    
    try {
        // Check if container already has a map instance
        if (container._leaflet_id) {
            console.log('Container already has Leaflet instance, removing...');
            delete container._leaflet_id;
        }
        
        map.value = L.map(container, {
            center: [officeCoordinates.lat, officeCoordinates.lng],
            zoom: 13,
            zoomAnimation: false,
            fadeAnimation: false,
            markerZoomAnimation: false,
            doubleClickZoom: false
        });

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map.value);
        
        // Add office marker
        const officeIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        
        officeMarker = L.marker([officeCoordinates.lat, officeCoordinates.lng], { 
            icon: officeIcon,
            title: 'สำนักงาน'
        })
            .bindPopup('สำนักงาน สมชายฟาร์ม<br>Office Location');
        
        // Only add to map if enabled and passes filter
        if (shouldShowOfficeMarker()) {
            officeMarker.addTo(map.value);
        }
        
        // Add pickup location markers
        pickupLocations.forEach((location, index) => {
            const pickupIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            const pickupMarker = L.marker([location.lat, location.lng], { 
                icon: pickupIcon,
                title: location.name
            })
                .bindPopup(`จุดรับสินค้า<br><strong>${location.name}</strong>`);
            
            // Only add to map if enabled and passes filter
            if (shouldShowPickupMarker(index)) {
                pickupMarker.addTo(map.value);
            }
            
            pickupMarkers.push(pickupMarker);
        });
        
        // Add delivery location markers
        deliveryLocations.forEach((location, index) => {
            const deliveryIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            const deliveryMarker = L.marker([location.lat, location.lng], { 
                icon: deliveryIcon,
                title: location.name
            })
                .bindPopup(`จุดส่งสินค้า<br><strong>${location.name}</strong>`);
            
            // Only add to map if enabled and passes filter
            if (shouldShowDeliveryMarker(index)) {
                deliveryMarker.addTo(map.value);
            }
            
            deliveryMarkers.push(deliveryMarker);
        });
            
        
        setTimeout(() => {
            if (map.value) {
                map.value.invalidateSize();
            }
        }, 100);
        
        console.log('Map initialized successfully');
    } catch (error) {
        console.error('Error initializing map:', error);
    }
};


// Plot route points on map for multiple vehicles
const plotRouteOnMap = async () => {
    if (!map.value) return;
    
    const L = await import('leaflet');
    
    // Clear existing markers and polylines
    routeMarkers.forEach(marker => map.value.removeLayer(marker));
    routeMarkers = [];
    stopMarkers.forEach(marker => map.value.removeLayer(marker));
    stopMarkers = [];
    
    // Clear existing polylines
    Object.values(routePolylines).forEach(polyline => {
        if (polyline) {
            map.value.removeLayer(polyline);
        }
    });
    routePolylines = {};
    
    console.log('plotRouteOnMap called:', {
        hasMap: !!map.value,
        vehicleCount: props.selectedDeviceIds.length,
        routeDataCollectionKeys: Object.keys(props.routeDataCollection),
        selectedDeviceIds: props.selectedDeviceIds,
        routeDataSample: Object.keys(props.routeDataCollection).map(key => ({
            deviceId: key,
            hasData: !!props.routeDataCollection[key],
            dataLength: props.routeDataCollection[key]?.list?.length || 0
        }))
    });
    
    if (Object.keys(props.routeDataCollection).length === 0) {
        console.log('No route data to plot');
        return;
    }
    
    const allRouteCoordinates: number[][] = [];
    
    // Plot routes for each vehicle with different colors
    props.selectedDeviceIds.forEach((deviceId, index) => {
        const routeData = props.routeDataCollection[deviceId];
        const dataToPlot = routeData?.list;
        
        if (!dataToPlot || dataToPlot.length === 0) {
            console.log(`No data for vehicle ${deviceId}`);
            return;
        }
        
        const routeCoordinates = dataToPlot.map((point: any) => [point.latitude, point.longitude]);
        allRouteCoordinates.push(...routeCoordinates);
        
        // Get color for this vehicle
        const color = vehicleColors[index % vehicleColors.length];
        
        // Draw polyline for this vehicle only if visible
        if (routeCoordinates.length > 1) {
            console.log(`Creating polyline for vehicle ${deviceId} with ${routeCoordinates.length} points, color: ${color}`);
            routePolylines[deviceId] = L.polyline(routeCoordinates, {
                color: color,
                weight: 3,
                opacity: 0.7,
                smoothFactor: 1
            });
            
            // Add to map only if visible
            if (routeVisibility.value[deviceId]) {
                routePolylines[deviceId].addTo(map.value);
            }
            console.log(`Polyline created for vehicle ${deviceId}, visible: ${routeVisibility.value[deviceId]}`);
        } else {
            console.log(`Not enough coordinates for vehicle ${deviceId}:`, routeCoordinates.length);
        }
    });
    
    // Create visible stop markers for route history stops
    routeHistory.value.forEach((stop, index) => {
        // Create different colored markers based on stop duration
        let markerColor = '#9CA3AF'; // Gray for short stops
        if (stop.duration >= 120) markerColor = '#7C3AED'; // Purple for very long stops
        else if (stop.duration >= 60) markerColor = '#DC2626'; // Red for long stops  
        else if (stop.duration >= 15) markerColor = '#EA580C'; // Orange for normal stops
        
        const stopIcon = L.divIcon({
            html: `<div style="background-color: ${markerColor}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
            iconSize: [12, 12],
            iconAnchor: [6, 6],
            popupAnchor: [0, -6],
            className: 'stop-marker'
        });
        
        // Format duration for popup
        const hours = Math.floor(stop.duration / 60);
        const mins = stop.duration % 60;
        const durationFormatted = `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
        
        const stopMarker = L.marker([stop.latitude, stop.longitude], { 
            icon: stopIcon
        })
            .bindPopup(`
                <div style="font-size: 12px;">
                    <strong>${stop.location}</strong><br>
                    รถ: ${stop.vehicleName}<br>
                    ระยะเวลา: ${durationFormatted}<br>
                    เริ่ม: ${stop.startTime}<br>
                    สิ้นสุด: ${stop.endTime}
                </div>
            `);
            
        // Only add to map if stop pins are enabled
        if (showStopPins.value) {
            stopMarker.addTo(map.value);
        }
            
        stopMarkers.push(stopMarker);
    });
    
    // Auto-focus map to show all route points
    if (allRouteCoordinates.length > 0) {
        try {
            const bounds = L.latLngBounds(allRouteCoordinates);
            if (bounds.isValid()) {
                console.log('Auto-focusing map on routes with', allRouteCoordinates.length, 'points');
                // Add a small delay to ensure map is fully rendered
                setTimeout(() => {
                    if (map.value) {
                        map.value.fitBounds(bounds, {
                            padding: [30, 30],
                            animate: true,
                            duration: 1.0,
                            maxZoom: 16 // Prevent zooming in too much
                        });
                    }
                }, 200);
            }
        } catch (error) {
            console.error('Error auto-focusing on routes:', error);
        }
    }
};

// Initialize map on mount
onMounted(() => {
    console.log('RouteMap component mounted');
    setTimeout(() => {
        console.log('Delayed initialization - checking for map container');
        const container = document.getElementById('mapContainer');
        console.log('Found container via getElementById:', container);
        if (container) {
            mapContainer.value = container as HTMLElement;
        }
        initializeMap();
    }, 100);
});

// Cleanup on unmount
onUnmounted(() => {
    console.log('RouteMap component unmounting, cleaning up map...');
    if (map.value) {
        map.value.remove();
        map.value = null;
    }
});

// Watch for marker visibility changes
watch([showOffice, showPickupPoints, showDeliveryPoints, showStopPins], () => {
    updateMarkersBasedOnCheckboxes();
});

// Debounced plot function to prevent multiple rapid redraws
let plotTimeout: NodeJS.Timeout | null = null;
const debouncedPlotRoute = () => {
    if (plotTimeout) clearTimeout(plotTimeout);
    plotTimeout = setTimeout(() => {
        if (Object.keys(props.routeDataCollection).length > 0) {
            plotRouteOnMap();
        }
    }, 100);
};

// Consolidated watcher for all route-affecting changes
watch([
    () => props.routeDataCollection,
    () => props.selectedDeviceIds, 
    () => props.routeAnalysisRadius
], () => {
    debouncedPlotRoute();
}, { immediate: true, deep: true });

// Watch for office hour changes to update utilization calculation
watch([() => props.officeHourStart, () => props.officeHourEnd], () => {
    // Utilization stats will automatically recalculate due to computed property
});

// Expose methods for parent component
defineExpose({
    showStopPopup,
    plotRouteOnMap
});
</script>