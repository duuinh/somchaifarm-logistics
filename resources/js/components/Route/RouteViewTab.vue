<template>
    <div class="grid grid-cols-1 gap-6" :class="selectedDeviceIds.length > 0 ? 'xl:grid-cols-[280px_1fr_1fr]' : 'xl:grid-cols-[280px_1fr]'">
        <!-- Configuration Section -->
        <ConfigSection
            v-model:selected-device-ids="selectedDeviceIds"
            v-model:selected-date="selectedDate"
            v-model:route-analysis-radius="routeAnalysisRadius"
            v-model:office-hour-start="officeHourStart"
            v-model:office-hour-end="officeHourEnd"
            :devices="devices"
            :vehicle-colors="vehicleColors"
            :has-cached-data="hasCachedData"
            :cached-vehicle-count="cachedVehicleCount"
            :is-loading="isLoading"
            :cache-stats="cacheStats"
            :cache-initialized="cacheInitialized"
            :cache-error="cacheError"
            @load-fresh-data="$emit('load-fresh-data')"
            @update-cache-stats="$emit('update-cache-stats')"
            @clear-cache="$emit('clear-cache')"
        />
        
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
                :route-history="routeHistory"
                :route-visibility="routeVisibility"
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
                                    :vehicle-color="getVehicleColor(selectedDeviceIds, vehicle.id)"
                                    :route-analysis-radius="routeAnalysisRadius"
                                    :office-hour-start="officeHourStart"
                                    :office-hour-end="officeHourEnd"
                                    :route-history="getVehicleRouteHistory(vehicle.id)"
                                    @show-stop-popup="(stopIndex) => $emit('show-stop-popup', vehicle.id, stopIndex)"
                                />
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import RouteMap from './RouteMap.vue';
import RouteAnalysis from './RouteAnalysis.vue';
import ConfigSection from './ConfigSection.vue';

interface Props {
    selectedDeviceIds: number[];
    selectedDate: string;
    routeAnalysisRadius: number;
    officeHourStart: number;
    officeHourEnd: number;
    devices: any[];
    vehicleColors: string[];
    devicesByType: any[];
    hasCachedData: boolean;
    cachedVehicleCount: number;
    isLoading: boolean;
    cacheStats: any;
    cacheInitialized: boolean;
    cacheError: string | null;
    routeDataCollection: Record<number, any>;
    routeHistory: any[];
    routeVisibility: Record<number, boolean>;
    getVehicleColor: (selectedIds: number[], deviceId: number) => string;
    getVehicleRouteHistory: (deviceId: number) => any[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:selectedDeviceIds': [value: number[]];
    'update:selectedDate': [value: string];
    'update:routeAnalysisRadius': [value: number];
    'update:officeHourStart': [value: number];
    'update:officeHourEnd': [value: number];
    'load-fresh-data': [];
    'update-cache-stats': [];
    'clear-cache': [];
    'show-stop-popup': [deviceId: number, stopIndex: number];
}>();

// Template refs
const routeMapRef = ref(null);

// Pass through v-model updates
const selectedDeviceIds = computed({
    get: () => props.selectedDeviceIds,
    set: (value) => emit('update:selectedDeviceIds', value)
});

const selectedDate = computed({
    get: () => props.selectedDate,
    set: (value) => emit('update:selectedDate', value)
});

const routeAnalysisRadius = computed({
    get: () => props.routeAnalysisRadius,
    set: (value) => emit('update:routeAnalysisRadius', value)
});

const officeHourStart = computed({
    get: () => props.officeHourStart,
    set: (value) => emit('update:officeHourStart', value)
});

const officeHourEnd = computed({
    get: () => props.officeHourEnd,
    set: (value) => emit('update:officeHourEnd', value)
});


// Expose the map ref for parent to access
defineExpose({
    routeMapRef
});
</script>