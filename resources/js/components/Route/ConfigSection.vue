<template>
    <div class="space-y-3">
        <!-- Vehicle Selection -->
        <VehicleSelector
            v-model="selectedDeviceIds"
            :devices="devices"
        />

        <!-- Date Selection -->
        <DateSelector v-model="selectedDate" />

        <!-- Route Analysis Radius -->
        <div>
            <Label class="text-xs font-medium">รัศมี (ม.)</Label>
            <div class="flex items-center gap-2 mt-1">
                <input
                    type="range"
                    v-model.number="routeAnalysisRadius"
                    min="50"
                    max="1000"
                    step="50"
                    class="flex-1"
                />
                <input
                    type="number"
                    v-model.number="routeAnalysisRadius"
                    min="50"
                    max="1000"
                    step="50"
                    class="h-8 w-16 px-2 text-center border border-gray-300 rounded text-sm"
                />
            </div>
        </div>
        
        <!-- Office Hours -->
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


        <!-- Action Buttons and Cache Status -->
        <div class="pt-2 space-y-2">
            <!-- Cache status indicator -->
            <div v-if="hasCachedData" class="text-xs text-green-600 bg-green-50 p-1.5 rounded border" 
                 :title="`มีข้อมูลแคชสำหรับ ${cachedVehicleCount} คันจากทั้งหมด ${selectedDeviceIds.length} คันที่เลือก`">
                📦 แคช {{ cachedVehicleCount }}/{{ selectedDeviceIds.length }}
            </div>
            
            <div class="flex gap-2">
                <Button 
                    @click="handleLoadFreshData"
                    :disabled="isLoading || selectedDeviceIds.length === 0 || !selectedDate"
                    variant="default"
                    size="sm"
                    class="h-7 flex-1 cursor-pointer"
                    title="ดึงข้อมูลใหม่จาก API"
                >
                    <Loader2 v-if="isLoading" class="mr-1 h-3 w-3 animate-spin" />
                    <RefreshCw v-else class="mr-1 h-3 w-3" />
                    {{ isLoading ? 'โหลด...' : 'ดึงข้อมูลใหม่' }}
                </Button>
            </div>
        </div>

        <!-- Cache Management -->
        <CacheManager
            :cache-stats="cacheStats"
            :cache-initialized="cacheInitialized"
            :cache-error="cacheError"
            @update-stats="$emit('update-cache-stats')"
            @clear-cache="$emit('clear-cache')"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Loader2, Play, RefreshCw } from 'lucide-vue-next';
import VehicleSelector from './VehicleSelector.vue';
import DateSelector from './DateSelector.vue';
import CacheManager from './CacheManager.vue';

interface Vehicle {
    id: number;
    name: string;
    type: string;
    category: string;
    shortname?: string;
}

interface CacheStats {
    indexedDB: {
        count: number;
        estimatedSize: number;
        oldestEntry: string | null;
        newestEntry: string | null;
    };
}

interface Props {
    selectedDeviceIds: number[];
    selectedDate: string;
    routeAnalysisRadius: number;
    officeHourStart: number;
    officeHourEnd: number;
    devices: Vehicle[];
    hasCachedData: boolean;
    cachedVehicleCount: number;
    isLoading: boolean;
    cacheStats: CacheStats;
    cacheInitialized: boolean;
    cacheError: string | null;
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
}>();

// Local state (removed API config)

// v-model bindings
const selectedDeviceIds = computed({
    get: () => props.selectedDeviceIds,
    set: (value) => emit('update:selectedDeviceIds', value)
});

const selectedDate = computed({
    get: () => props.selectedDate,
    set: (value) => emit('update:selectedDate', value)
});


const officeHourStart = computed({
    get: () => props.officeHourStart,
    set: (value) => emit('update:officeHourStart', Number(value))
});

const routeAnalysisRadius = computed({
    get: () => props.routeAnalysisRadius,
    set: (value) => emit('update:routeAnalysisRadius', Number(value))
});

const officeHourEnd = computed({
    get: () => props.officeHourEnd,
    set: (value) => emit('update:officeHourEnd', Number(value))
});


// Click handler for fresh data
const handleLoadFreshData = () => {
    console.log('Loading fresh data from API...');
    emit('load-fresh-data');
};
</script>