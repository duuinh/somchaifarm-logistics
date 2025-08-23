import { computed, unref } from 'vue';
import { useStopAnalysis } from './useStopAnalysis';

export function useRouteHistory(
    selectedDeviceIds: any,
    routeDataCollection: any,
    devices: any, // This should be the reactive devices array or computed
    routeAnalysisRadius: any
) {
    const { analyzeRouteStops, processStops } = useStopAnalysis();
    

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

    // Centralized route history calculation
    const allRouteHistory = computed(() => {
        const allStops = [];
        // Properly access the reactive radius value
        const currentRadius = unref(routeAnalysisRadius);
            
        
        selectedDeviceIds.value.forEach(deviceId => {
            const routeData = routeDataCollection.value[deviceId];
            const dataToAnalyze = routeData?.list;
            if (!dataToAnalyze || dataToAnalyze.length === 0) return;
            const devicesArray = unref(devices) || [];
            const vehicleName = devicesArray.find(d => d.id === deviceId)?.name || `Vehicle ${deviceId}`;
            const rawStops = analyzeRouteStops(dataToAnalyze, currentRadius, deviceId, vehicleName);
            const processedStops = processStops(rawStops, 5, currentRadius);
            allStops.push(...processedStops);
        });
        return allStops.sort((a, b) => new Date(a.startTime).getTime() - new Date(b.startTime).getTime());
    });

    // Get route history for specific vehicle
    const getVehicleRouteHistory = (deviceId: number) => {
        return allRouteHistory.value.filter(stop => stop.deviceId === deviceId);
    };

    return {
        stopPoints,
        allRouteHistory,
        getVehicleRouteHistory
    };
}