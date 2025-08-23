import { ref, watch } from 'vue';
import { useIndexedDBCache } from './useIndexedDBCache';
import { useMultiProviderAPI } from './useMultiProviderAPI';

// API Configuration (single source of truth)
const authorizationHeader = ref(localStorage.getItem('route-history-auth') || '');
const tokenHeader = ref(localStorage.getItem('route-history-token') || '');

// Debug: Check what's loaded from localStorage
console.log('Loading from localStorage:', {
    auth: localStorage.getItem('route-history-auth') || 'Not found',
    token: localStorage.getItem('route-history-token') || 'Not found',
    authRef: authorizationHeader.value ? 'Present in ref' : 'Empty in ref',
    tokenRef: tokenHeader.value ? 'Present in ref' : 'Empty in ref'
});

// Watch and save to localStorage
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

export function useRouteAPI(devices: any[] = []) {
    const isLoading = ref(false);
    const apiError = ref('');
    const loadingStates = ref<Record<number, boolean>>({});
    
    // Initialize multi-provider API if devices are provided
    const { fetchRouteDataForProvider } = devices.length > 0 
        ? useMultiProviderAPI(devices) 
        : { fetchRouteDataForProvider: null };

    // Cache management (IndexedDB only)
    const getCachedData = async (deviceId: number, date: string) => {
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
        if (cacheInitialized.value) {
            try {
                const deletedCount = await clearExpiredCache();
                console.log(`Cleared ${deletedCount} expired IndexedDB cache entries`);
            } catch (error) {
                console.error('Error clearing old IndexedDB cache:', error);
            }
        }
    };

    // Fetch single vehicle data
    const fetchSingleVehicleData = async (deviceId: number, date: string, forceFresh = false) => {
        loadingStates.value[deviceId] = true;
        
        try {
            // Check cache first (unless forcing fresh data)
            if (!forceFresh) {
                const cachedData = await getCachedData(deviceId, date);
                if (cachedData) {
                    console.log(`Using cached data for device ${deviceId} on ${date}`);
                    return cachedData;
                }
            }
            
            // Determine provider for this device
            const deviceConfig = devices.find(d => d.id === deviceId);
            const provider = deviceConfig?.provider || 'andaman';
            
            let data;
            if (fetchRouteDataForProvider && deviceConfig?.provider) {
                // Use multi-provider system
                console.log(`Fetching fresh data for device ${deviceId} on ${date} from provider: ${provider}`);
                data = await fetchRouteDataForProvider(provider, deviceId, date);
            } else {
                // Fallback to legacy Andaman API
                console.log(`Fetching fresh data for device ${deviceId} on ${date} using legacy API`);
                const response = await fetch('https://apitracking.andamantracking.dev/web/v1/passroute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': authorizationHeader.value,
                        'Token': tokenHeader.value,
                    },
                    body: JSON.stringify({
                        deviceId: deviceId,
                        start: new Date(date + 'T00:00:00').getTime(),
                        end: new Date(date + 'T23:59:59').getTime()
                    })
                });

                if (!response.ok) {
                    throw new Error(`API error: ${response.status}`);
                }

                data = await response.json();
            }
            
            // Cache the successful response
            await setCachedData(deviceId, date, data);
            
            return data;
        } catch (error) {
            console.error(`Error fetching data for device ${deviceId}:`, error);
            throw error;
        } finally {
            loadingStates.value[deviceId] = false;
        }
    };

    // Fetch data for multiple vehicles
    const fetchRouteData = async (selectedDeviceIds: number[], selectedDate: string, forceFresh = false) => {
        if (selectedDeviceIds.length === 0 || !selectedDate) {
            apiError.value = 'กรุณาเลือกรถและวันที่';
            return {};
        }

        // Check if we have credentials for at least one provider
        const hasAndamanCreds = authorizationHeader.value && tokenHeader.value;
        const hasSiamGpsCreds = localStorage.getItem('siamgps-authorization');
        
        if (!hasAndamanCreds && !hasSiamGpsCreds) {
            apiError.value = 'กรุณากรอกข้อมูล API credentials';
            return {};
        }

        isLoading.value = true;
        apiError.value = '';
        const routeDataCollection: Record<number, any> = {};

        try {
            // Store tokens (only if they exist)
            if (authorizationHeader.value) {
                localStorage.setItem('route-history-auth', authorizationHeader.value);
            }
            if (tokenHeader.value) {
                localStorage.setItem('route-history-token', tokenHeader.value);
            }

            // Fetch data for all selected vehicles concurrently
            const promises = selectedDeviceIds.map(deviceId => 
                fetchSingleVehicleData(deviceId, selectedDate, forceFresh)
                    .then(data => ({ deviceId, data }))
                    .catch(error => ({ deviceId, error }))
            );
            
            const results = await Promise.all(promises);
            
            // Process results
            results.forEach(result => {
                if ('data' in result && result.data) {
                    routeDataCollection[result.deviceId] = result.data;
                } else {
                    console.error(`Failed to fetch data for device ${result.deviceId}`);
                    routeDataCollection[result.deviceId] = null;
                }
            });

            if (Object.values(routeDataCollection).every(data => data === null)) {
                apiError.value = 'ไม่สามารถดึงข้อมูลได้ กรุณาตรวจสอบ API credentials';
            }

            return routeDataCollection;
        } catch (error) {
            console.error('Error in fetchRouteData:', error);
            apiError.value = `เกิดข้อผิดพลาด: ${error}`;
            return {};
        } finally {
            isLoading.value = false;
        }
    };

    // Calculate utilization from route data
    const calculateUtilizationFromData = (data: any, officeHourStart: number, officeHourEnd: number) => {
        if (!data?.list || data.list.length === 0) return 0;
        
        // Filter for office hours
        const officeHourData = data.list.filter((point: any) => {
            const hour = new Date(point.event_stamp).getHours();
            return hour >= officeHourStart && hour < officeHourEnd;
        });
        
        if (officeHourData.length === 0) return 0;
        
        // Use theoretical office hours instead of GPS timestamps
        const theoreticalOfficeHours = officeHourEnd - officeHourStart; // hours
        const totalOfficeTime = theoreticalOfficeHours * 60; // convert to minutes
        
        // Deduct lunch break
        const effectiveWorkTime = Math.max(0, totalOfficeTime - 60);
        
        // Calculate moving time
        const movingPoints = officeHourData.filter((point: any) => point.speed > 0).length;
        const movingTimeRatio = officeHourData.length > 0 ? movingPoints / officeHourData.length : 0;
        const movingTime = totalOfficeTime * movingTimeRatio;
        
        const utilization = effectiveWorkTime > 0 ? Math.round((movingTime / effectiveWorkTime) * 100) : 0;
        return Math.min(utilization, 100); // Cap at 100%
    };

    // Fetch utilization data for past N days or date range
    const fetchUtilizationData = async (selectedDeviceIds: number[], officeHourStart: number, officeHourEnd: number, days: number = 7, startDate?: string, endDate?: string) => {
        if (selectedDeviceIds.length === 0) {
            return {};
        }
        
        const results: Record<string, any> = {};
        
        try {
            // Get dates - either from date range or past N days
            const dates = [];
            if (startDate && endDate) {
                // Use custom date range
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    dates.push(d.toISOString().split('T')[0]);
                }
            } else {
                // Use past N days
                for (let i = days - 1; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    dates.push(date.toISOString().split('T')[0]);
                }
            }
            
            // Fetch data for each date and vehicle
            for (const date of dates) {
                results[date] = {};
                
                for (const deviceId of selectedDeviceIds) {
                    // Check cache first
                    const cachedData = await getCachedData(deviceId, date);
                    
                    if (cachedData) {
                        const utilization = calculateUtilizationFromData(cachedData, officeHourStart, officeHourEnd);
                        results[date][deviceId] = utilization;
                    } else {
                        // Fetch fresh data if not cached
                        try {
                            const data = await fetchSingleVehicleData(deviceId, date);
                            const utilization = calculateUtilizationFromData(data, officeHourStart, officeHourEnd);
                            results[date][deviceId] = utilization;
                        } catch (error) {
                            console.error(`Error fetching data for device ${deviceId} on ${date}:`, error);
                            results[date][deviceId] = 0;
                        }
                    }
                }
            }
            
            return results;
        } catch (error) {
            console.error('Error fetching utilization data:', error);
            return {};
        }
    };

    return {
        isLoading,
        apiError,
        loadingStates,
        authorizationHeader, // These are the refs that will be watched for localStorage
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
    };
}