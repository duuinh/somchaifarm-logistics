import { officeLocations, pickupLocations, deliveryLocations, calculateDistance } from '@/composables/useRouteFiltering';

interface StopPoint {
    startTime: string;
    endTime: string;
    location: string;
    latitude: number;
    longitude: number;
    duration: number;
    durationFormatted?: string;
    deviceId?: number;
    vehicleName?: string;
    distanceFromPrevious?: number;
}

// Helper function to find the closest stop point label
export const findClosestStopPoint = (latitude: number, longitude: number, radius: number) => {
    const allLocations = [
        ...pickupLocations.value,
        ...deliveryLocations.value,
        ...officeLocations.value
    ];
    
    let closestLocation = null;
    let minDistance = Infinity;
    
    allLocations.forEach(location => {
        const distance = calculateDistance(latitude, longitude, location.lat, location.lng);
        if (distance < minDistance && distance <= radius) {
            minDistance = distance;
            closestLocation = location;
        }
    });
    
    return closestLocation;
};

// Helper function to get location name for a GPS point
export const getLocationName = (point: any, index: number, radius: number) => {
    
    const closestStopPoint = findClosestStopPoint(point.latitude, point.longitude, radius);
    
    // Priority: 1) Closest known location, 2) Previously saved name for same coordinates, 3) Siam GPS sGeO, 4) Address, 5) Generic point number
    if (closestStopPoint) {
        return closestStopPoint.name;
    }
    
    // Check if we've seen this location before and have a saved name
    // This could be implemented by checking a location cache/database
    // For now, use the GPS provider data as the "saved" name
    
    // Use Siam GPS sGeO field as saved name (this is provider's saved location name)
    if (point.geoLocation?.sGeO && point.geoLocation.sGeO.trim() !== '' && point.geoLocation.sGeO !== 'ไม่ระบุตำแหน่ง') {
        return point.geoLocation.sGeO;
    }
    
    // Use address field as saved name
    if (point.address && point.address.trim() !== '' && point.address !== 'ไม่ระบุตำแหน่ง') {
        return point.address;
    }
    
    return `จุดที่ ${index + 1}`;
};

// Helper function to format duration
export const formatDuration = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
};

// Helper function to calculate distance between two GPS points
export const calculateDistanceBetweenPoints = (lat1: number, lng1: number, lat2: number, lng2: number) => {
    return calculateDistance(lat1, lng1, lat2, lng2) / 1000; // Convert to km
};

// Main function to analyze route data and extract stops
export const analyzeRouteStops = (
    routeData: any[], 
    radius: number, 
    deviceId?: number, 
    vehicleName?: string
): StopPoint[] => {
    if (!routeData || routeData.length === 0) return [];
    
    const stops: StopPoint[] = [];
    let currentStop: StopPoint | null = null;
    
    routeData.forEach((point: any, index: number) => {
        if (point.speed === 0) {
            if (!currentStop) {
                // Start of a new stop
                const locationName = getLocationName(point, index, radius);
                
                currentStop = {
                    startTime: point.event_stamp,
                    endTime: point.event_stamp,
                    location: locationName,
                    latitude: point.latitude,
                    longitude: point.longitude,
                    duration: 0,
                    durationFormatted: '00:00',
                    deviceId,
                    vehicleName
                };
            } else {
                // Continue existing stop
                currentStop.endTime = point.event_stamp;
            }
        } else {
            if (currentStop) {
                // End of stop, calculate duration and add to stops
                const start = new Date(currentStop.startTime);
                const end = new Date(currentStop.endTime);
                const durationMs = end.getTime() - start.getTime();
                currentStop.duration = Math.round(durationMs / 1000 / 60); // minutes
                currentStop.durationFormatted = formatDuration(currentStop.duration);
                
                stops.push(currentStop);
                currentStop = null;
            }
        }
    });
    
    // Add the last stop if exists
    if (currentStop) {
        const start = new Date(currentStop.startTime);
        const end = new Date(currentStop.endTime);
        const durationMs = end.getTime() - start.getTime();
        currentStop.duration = Math.round(durationMs / 1000 / 60);
        currentStop.durationFormatted = formatDuration(currentStop.duration);
        stops.push(currentStop);
    }
    
    return stops;
};

// Filter and combine stops
export const processStops = (stops: StopPoint[], minDuration: number = 5, radius: number = 50): StopPoint[] => {
    
    // Filter out very brief stops
    const filteredStops = stops.filter(stop => stop.duration >= minDuration);
    
    // Combine consecutive stops at the same location
    const combinedStops: StopPoint[] = [];
    
    for (let i = 0; i < filteredStops.length; i++) {
        const currentStop = filteredStops[i];
        
        // Check if this stop is at the same location as the previous one
        // Use both location name AND GPS coordinate proximity for grouping
        const shouldCombine = combinedStops.length > 0 && 
            combinedStops[combinedStops.length - 1].deviceId === currentStop.deviceId && (
                // Same location name
                combinedStops[combinedStops.length - 1].location === currentStop.location ||
                // OR GPS coordinates are very close (within configurable radius)
                calculateDistance(
                    combinedStops[combinedStops.length - 1].latitude,
                    combinedStops[combinedStops.length - 1].longitude,
                    currentStop.latitude,
                    currentStop.longitude
                ) <= radius
            );
            
        if (shouldCombine) {
            // Combine with previous stop
            const prevStop = combinedStops[combinedStops.length - 1];
            prevStop.endTime = currentStop.endTime;
            prevStop.duration += currentStop.duration;
            prevStop.durationFormatted = formatDuration(prevStop.duration);
            
            // Use the better location name (prefer known locations > addresses > generic)
            const prevIsGeneric = prevStop.location.startsWith('จุดที่');
            const currentIsGeneric = currentStop.location.startsWith('จุดที่');
            // Check if stops are at known locations (from database)
            const allKnownLocations = [
                ...pickupLocations.value.map(l => l.name),
                ...deliveryLocations.value.map(l => l.name),
                ...officeLocations.value.map(l => l.name)
            ];
            const prevIsKnown = allKnownLocations.some(locationName => prevStop.location.includes(locationName));
            const currentIsKnown = allKnownLocations.some(locationName => currentStop.location.includes(locationName));
            
            // Priority: Known locations > Addresses > Generic names
            if (currentIsKnown && !prevIsKnown) {
                prevStop.location = currentStop.location;
            } else if (!currentIsGeneric && prevIsGeneric) {
                prevStop.location = currentStop.location;
            }
        } else {
            // Add as new stop with distance calculation
            const stopWithDistance = { ...currentStop };
            
            // Calculate distance from previous stop using GPS coordinates
            if (combinedStops.length > 0) {
                const previousStop = combinedStops[combinedStops.length - 1];
                stopWithDistance.distanceFromPrevious = calculateDistanceBetweenPoints(
                    previousStop.latitude, 
                    previousStop.longitude, 
                    currentStop.latitude, 
                    currentStop.longitude
                );
            }
            
            combinedStops.push(stopWithDistance);
        }
    }
    
    return combinedStops;
};

export const useStopAnalysis = () => {
    return {
        findClosestStopPoint,
        getLocationName,
        formatDuration,
        calculateDistanceBetweenPoints,
        analyzeRouteStops,
        processStops
    };
};