<template>
    <div class="space-y-4">
        <!-- Route History Box -->
        <div v-if="hasRouteHistory" class="p-4 rounded-lg border-2" 
             :style="{ 
                 backgroundColor: props.vehicleColor ? `${props.vehicleColor}08` : '#dbeafe',
                 borderColor: props.vehicleColor || '#93c5fd'
             }">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <!-- Vehicle color indicator -->
                    <div v-if="props.vehicleColor" 
                         class="w-3 h-3 rounded-full border flex-shrink-0"
                         :style="{ backgroundColor: props.vehicleColor, borderColor: props.vehicleColor }">
                    </div>
                    <div class="text-sm font-medium min-w-0 flex-1"
                         :style="{ color: props.vehicleColor || '#1d4ed8' }">
                        ประวัติเส้นทาง
                        <span v-if="props.deviceName" class="text-xs font-normal block mt-1 truncate"
                              :style="{ color: props.vehicleColor ? `${props.vehicleColor}cc` : '#2563eb' }"
                              :title="props.deviceName">
                            {{ props.deviceName }}
                        </span>
                    </div>
                </div>
                <span v-if="props.vehicleType" class="text-xs px-2 py-1 rounded-full font-medium flex-shrink-0"
                      :style="{ 
                          backgroundColor: props.vehicleColor ? `${props.vehicleColor}20` : '#dbeafe',
                          color: props.vehicleColor || '#1e40af'
                      }">
                    {{ props.vehicleType }}
                </span>
            </div>
            
            <!-- Utilization stats only -->
            <div class="text-xs mb-2" :style="{ color: props.vehicleColor ? `${props.vehicleColor}dd` : '#2563eb' }">
                <div class="flex items-center gap-4">
                    <span>การใช้งาน: 
                        <span :class="[
                            'font-bold',
                            utilizationStats.utilization >= 30 ? 'text-green-600' :
                            utilizationStats.utilization >= 15 ? 'text-orange-600' :
                            'text-red-600'
                        ]">{{ utilizationStats.utilization }}%</span>
                    </span>
                    <span class="text-gray-500">
                        (เคลื่อนที่ {{ formatDuration(utilizationStats.movingTime) }}/{{ formatDuration(utilizationStats.effectiveWorkTime) }} ชม.ทำงาน)
                    </span>
                    <span v-if="totalDistanceTraveled > 0" class="text-blue-600 font-medium flex items-center gap-1">
                        <Car class="w-3 h-3" />
                        รวม {{ totalDistanceTraveled.toFixed(1) }} กม.
                    </span>
                </div>
            </div>
            
            <!-- Timeline View -->
            <div class="p-3 bg-white rounded border border-blue-200">
                <div class="space-y-3">
                    <template v-for="(stop, index) in routeHistory" :key="`timeline-${index}`">
                        <!-- Day period separator -->
                        <div v-if="index === 0 || getDayPeriod(stop.startTime) !== getDayPeriod(routeHistory[index - 1].startTime)" 
                             class="flex items-center gap-2 mb-2 mt-4 first:mt-0">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <div :class="['text-xs px-2 py-1 rounded-full flex items-center gap-1', getPeriodInfo(getDayPeriod(stop.startTime)).color]">
                                <Sunrise v-if="getPeriodInfo(getDayPeriod(stop.startTime)).icon === 'sunrise'" class="w-3 h-3" />
                                <Sun v-else-if="getPeriodInfo(getDayPeriod(stop.startTime)).icon === 'sun'" class="w-3 h-3" />
                                <Moon v-else-if="getPeriodInfo(getDayPeriod(stop.startTime)).icon === 'moon'" class="w-3 h-3" />
                                {{ getPeriodInfo(getDayPeriod(stop.startTime)).label }}
                            </div>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <!-- Timeline dot -->
                            <div class="flex flex-col items-center mt-1">
                                <div 
                                    :class="[
                                        'w-3 h-3 rounded-full border-2',
                                        stop.duration >= 120 ? 'bg-purple-500 border-purple-600' : 
                                        stop.duration >= 60 ? 'bg-red-500 border-red-600' : 
                                        stop.duration >= 15 ? 'bg-orange-500 border-orange-600' :
                                        'bg-gray-400 border-gray-500'
                                    ]"
                                ></div>
                                <div v-if="index < routeHistory.length - 1" class="w-0.5 h-8 bg-gray-300 mt-1"></div>
                            </div>
                            
                            <!-- Timeline content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <button
                                                @click="$emit('show-stop-popup', index)"
                                                class="text-sm font-medium text-gray-900 hover:text-blue-600 cursor-pointer text-left truncate max-w-[300px]"
                                                :title="stop.location"
                                            >
                                                {{ stop.location }}
                                            </button>
                                            <!-- Time period indicators -->
                                            <span v-if="getTimePeriodType(stop.startTime, stop.endTime) === 'lunch'" 
                                                  class="text-xs bg-yellow-100 text-yellow-800 px-1 rounded flex items-center" 
                                                  title="ช่วงพักกลางวัน">
                                                <Utensils class="w-3 h-3" />
                                            </span>
                                            <!-- Distance on same line as name -->
                                            <span v-if="stop.distanceFromPrevious !== undefined" class="text-xs text-blue-600 font-medium flex items-center gap-1">
                                                <Car class="w-3 h-3" />
                                                {{ stop.distanceFromPrevious.toFixed(1) }} กม.
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                            <span>{{ formatTime(stop.startTime) }}-{{ formatTime(stop.endTime) }}</span>
                                            <span 
                                                :class="[
                                                    'font-bold flex items-center gap-1',
                                                    stop.duration >= 120 ? 'text-purple-700' : 
                                                    stop.duration >= 60 ? 'text-red-600' : 
                                                    stop.duration >= 15 ? 'text-orange-600' :
                                                    'text-gray-600'
                                                ]"
                                            >
                                                <Clock class="w-3 h-3" />
                                                {{ stop.durationFormatted }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-600 mt-2 pt-2 border-t border-blue-200 flex-wrap">
                <label class="flex items-center gap-1 cursor-pointer">
                    <input 
                        type="checkbox" 
                        v-model="showShortStops" 
                        class="rounded border-gray-300 text-primary focus:ring-primary text-xs"
                    />
                    <div class="w-3 h-3 bg-gray-100 border border-gray-300 rounded"></div>
                    <span>สั้นๆ (00:05-00:14)</span>
                </label>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-orange-100 border border-orange-300 rounded"></div>
                    <span>ไม่นาน (00:15-00:59)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-red-100 border border-red-400 rounded"></div>
                    <span>นาน (01:00-01:59)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-purple-100 border-2 border-purple-500 rounded"></div>
                    <span class="font-medium text-purple-700">นานมาก (02:00+)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs bg-yellow-100 text-yellow-800 px-1 rounded flex items-center">
                        <Utensils class="w-3 h-3" />
                    </span>
                    <span>พักกลางวัน</span>
                </div>
                <div class="flex items-center gap-1">
                    <Clock class="w-3 h-3 text-gray-600" />
                    <span>ระยะเวลา</span>
                </div>
                <div class="flex items-center gap-1">
                    <Car class="w-3 h-3 text-blue-600" />
                    <span>การกระจัด (กม.)</span>
                </div>
            </div>
        </div>
        
        <!-- Show message when no stops found -->
        <div v-else class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="text-sm font-medium text-blue-700 mb-2">ประวัติเส้นทาง</div>
            <div class="text-sm text-gray-600">
                ไม่พบประวัติ
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Car, Clock, Sun, Sunrise, Moon, Utensils } from 'lucide-vue-next';
import { calculateDistance } from '@/composables/useRouteFiltering';
import { useStopAnalysis } from '@/composables/route/useStopAnalysis';

interface Stop {
    startTime: string;
    endTime: string;
    location: string;
    latitude: number;
    longitude: number;
    duration: number;
    durationFormatted: string;
    distanceFromPrevious?: number; // km from previous stop
}

interface Props {
    routeData?: any;
    deviceId?: number;
    deviceName?: string;
    vehicleType?: string;
    vehicleColor?: string;
    routeAnalysisRadius: number;
    officeHourStart: number;
    officeHourEnd: number;
    routeHistory?: Stop[]; // Pre-calculated route history
}

const props = defineProps<Props>();

defineEmits<{
    'show-stop-popup': [index: number]
}>();

// Show/hide short stops toggle
const showShortStops = ref(true);

// Use shared stop analysis logic
const { analyzeRouteStops, processStops, formatDuration: formatDurationUtil } = useStopAnalysis();

// Helper function to format minutes to HH:MM (keep local for backward compatibility)
const formatDuration = formatDurationUtil;

// Helper function to format timestamp to time
const formatTime = (timestamp: string) => {
    try {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('th-TH', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
    } catch (error) {
        return timestamp;
    }
};

// Helper function to determine time period type
const getTimePeriodType = (startTime: string, endTime: string) => {
    const start = new Date(startTime);
    const end = new Date(endTime);
    const startHour = start.getHours();
    const endHour = end.getHours();
    const startMinute = start.getMinutes();
    const endMinute = end.getMinutes();
    
    // Calculate duration in hours
    const durationHours = (end.getTime() - start.getTime()) / (1000 * 60 * 60);
    
    // Check if start or end time is clearly out of office hours
    const startsOutOfOffice = startHour < props.officeHourStart || startHour >= props.officeHourEnd;
    const endsOutOfOffice = endHour < props.officeHourStart || endHour >= props.officeHourEnd;
    
    // Lunch break: specifically during 12:00-13:00, at least 30 minutes, and not a very long stop
    const isLunchTime = (startHour === 12 || (startHour === 11 && startMinute >= 30)) && 
                        (endHour === 12 || endHour === 13 || (endHour === 14 && endMinute <= 30)) &&
                        durationHours >= 0.5 && durationHours <= 3;
    
    // Out of office: any stop that starts or ends outside office hours
    const isOutOfOffice = startsOutOfOffice || endsOutOfOffice;
    
    if (isLunchTime) return 'lunch';
    if (isOutOfOffice) return 'out-of-office';
    return 'normal';
};

// Helper function to determine day period for visual separation
const getDayPeriod = (startTime: string) => {
    const hour = new Date(startTime).getHours();
    if (hour >= 6 && hour < 12) return 'morning';
    if (hour >= 12 && hour < 18) return 'afternoon';
    return 'night';
};

// Helper function to get period label and icon
const getPeriodInfo = (period: string) => {
    switch (period) {
        case 'morning':
            return { label: 'เช้า', icon: 'sunrise', color: 'bg-orange-100 text-orange-800' };
        case 'afternoon':
            return { label: 'บ่าย', icon: 'sun', color: 'bg-yellow-100 text-yellow-800' };
        case 'night':
            return { label: 'กลางคืน', icon: 'moon', color: 'bg-blue-100 text-blue-800' };
        default:
            return { label: '', icon: '', color: '' };
    }
};

// Removed - now using shared logic from useStopAnalysis

// Use pre-calculated route history or fallback to local calculation
const routeHistory = computed(() => {
    // Use pre-calculated data if available
    if (props.routeHistory && props.routeHistory.length > 0) {
        // Filter out short stops if checkbox is unchecked
        if (!showShortStops.value) {
            return props.routeHistory.filter(stop => stop.duration >= 15);
        }
        return props.routeHistory;
    }
    
    // Fallback to local calculation for backward compatibility
    const dataToAnalyze = props.routeData?.list;
    if (!dataToAnalyze || dataToAnalyze.length === 0) return [];
    
    const rawStops = analyzeRouteStops(
        dataToAnalyze, 
        props.routeAnalysisRadius,
        props.deviceId,
        props.deviceName
    );
    
    const processedStops = processStops(rawStops, 5);
    
    if (!showShortStops.value) {
        return processedStops.filter(stop => stop.duration >= 15);
    }
    
    return processedStops;
});

// Computed property to check if there is route history to show
const hasRouteHistory = computed(() => {
    return routeHistory.value.length > 0;
});

// Computed property to calculate total distance traveled using GPS data
const totalDistanceTraveled = computed(() => {
    // Primary: Use the distance_all field from the root of GPS data (total route distance)
    if (props.routeData?.distance_all) {
        const distanceAll = parseFloat(props.routeData.distance_all);
        if (distanceAll > 0) {
            return distanceAll / 1000; // Convert meters to km
        }
    }
    
    // Fallback: sum up distances between stops using GPS coordinates
    return routeHistory.value.reduce((total, stop) => {
        return total + (stop.distanceFromPrevious || 0);
    }, 0);
});

// Computed property to calculate utilization percentage based on office hours
const utilizationStats = computed(() => {
    if (!props.routeData?.list || props.routeData.list.length === 0) {
        return { utilization: 0, movingTime: 0, totalTime: 0 };
    }
    
    const data = props.routeData.list;
    
    // Filter data to only include points during office hours
    const officeHourData = data.filter((point: any) => {
        const timestamp = new Date(point.event_stamp);
        const hour = timestamp.getHours();
        return hour >= props.officeHourStart && hour < props.officeHourEnd;
    });
    
    if (officeHourData.length === 0) {
        return { utilization: 0, movingTime: 0, totalTime: 0 };
    }
    
    // Use theoretical office hours instead of GPS timestamps
    const theoreticalOfficeHours = props.officeHourEnd - props.officeHourStart; // hours
    const totalOfficeTime = theoreticalOfficeHours * 60; // convert to minutes
    
    // Deduct lunch break time (assume 1 hour lunch break from 12:00-13:00)
    const lunchBreakMinutes = 60;
    const effectiveWorkTime = Math.max(0, totalOfficeTime - lunchBreakMinutes);
    
    // Calculate time spent moving during office hours
    const movingPoints = officeHourData.filter((point: any) => point.speed > 0).length;
    const totalPoints = officeHourData.length;
    
    // Estimate moving time based on point distribution during office hours
    const movingTimeRatio = totalPoints > 0 ? movingPoints / totalPoints : 0;
    const movingTime = totalOfficeTime * movingTimeRatio;
    
    // Utilization = moving time / effective work time (excluding lunch)
    const utilization = effectiveWorkTime > 0 ? Math.round((movingTime / effectiveWorkTime) * 100) : 0;
    
    return {
        utilization,
        movingTime: Math.round(movingTime),
        totalTime: Math.round(totalOfficeTime),
        effectiveWorkTime: Math.round(effectiveWorkTime)
    };
});
</script>