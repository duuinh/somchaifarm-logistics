import { ref } from 'vue';

// API Provider configurations
const API_PROVIDERS = {
    andaman: {
        name: 'Andaman Tracking',
        realtimeUrl: 'https://apitracking.andamantracking.dev/web/v1/home',
        routeUrl: 'https://apitracking.andamantracking.dev/web/v1/passroute',
        requiresAuth: true,
        authHeaders: ['Authorization', 'Token']
    },
    siamgps: {
        name: 'Siam GPS Track',
        baseUrl: '/api/siamgps', // Use Laravel proxy
        routeUrl: '/api/siamgps/route-history',
        realtimeUrl: '/api/siamgps/realtime/listByVehicleId',
        requiresAuth: true,
        authHeaders: ['Authorization'],
        transformRouteResponse: (data: any) => {
            // Transform Siam GPS response to match Andaman format
            if (!data || !data.data || !Array.isArray(data.data)) return { list: [] };
            
            return {
                list: data.data.map((point: any, index: number) => {
                    const transformedPoint = {
                        latitude: point.location?.coordinates?.[1] || 0,
                        longitude: point.location?.coordinates?.[0] || 0,
                        speed: point.speed || 0,
                        event_stamp: point.time || new Date().toISOString(),
                        direction: point.heading || 0,
                        ignition: point.vehicleStatus === 'RUNNING' ? 1 : 0,
                        satellites: point.gpsFix ? 10 : 0,
                        address: point.geoLocation?.sGeO || 'ไม่ระบุตำแหน่ง',
                        geoLocation: point.geoLocation // Keep original geoLocation for direct access
                    };
                    
                    // Debug first few points
                    if (index < 3) {
                        console.log('Siam GPS Point Transform:', {
                            original: point.geoLocation,
                            transformed: transformedPoint.address
                        });
                    }
                    
                    return transformedPoint;
                })
            };
        },
        transformRealtimeResponse: (data: any) => {
            // Transform realtime data to match our format
            if (!data) return null;
            
            return {
                device_id: data.id || data.deviceId,
                name: data.name || data.vehicleName,
                latitude: data.latitude || data.lat,
                longitude: data.longitude || data.lon || data.lng,
                speed: data.speed || 0,
                status_name: data.status || 'Stop',
                online: data.online ? 1 : 0,
                event_stamp: data.timestamp || data.dateTime || new Date().toISOString(),
                address: data.address || 'ไม่ระบุ',
                fuel_liters: data.fuel || '-',
                ignition: data.ignition || 0,
                satellites: data.satellites || 0
            };
        }
    }
};

export function useMultiProviderAPI(devices: any[] = []) {
    // Store credentials for different providers
    const providerCredentials = ref<Record<string, any>>({
        andaman: {
            Authorization: localStorage.getItem('route-history-auth') || '',
            Token: localStorage.getItem('route-history-token') || ''
        },
        siamgps: {
            Authorization: localStorage.getItem('siamgps-authorization') || ''
        }
    });

    // Fetch realtime data from appropriate provider
    const fetchRealtimeDataForProvider = async (provider: string, deviceIds: number[], currentDevices?: any[]) => {
        const devicesToUse = currentDevices || devices;
        const config = API_PROVIDERS[provider];
        if (!config) {
            console.error(`Unknown provider: ${provider}`);
            return [];
        }

        try {
            const headers: any = {
                'Content-Type': 'application/json'
            };

            // Prepare credentials
            let creds = {};
            if (config.requiresAuth && providerCredentials.value[provider]) {
                creds = { ...providerCredentials.value[provider] };
                
                // For Siam GPS, ensure Authorization header has Bearer prefix
                if (provider === 'siamgps' && creds.Authorization && !creds.Authorization.startsWith('Bearer ')) {
                    creds.Authorization = `Bearer ${creds.Authorization}`;
                }
                
                // For non-Siam GPS providers, add to headers
                if (provider !== 'siamgps') {
                    Object.assign(headers, creds);
                }
            }

            // For Siam GPS, we need to call the API for each vehicle individually via Laravel proxy
            if (provider === 'siamgps') {
                const allVehicleData = [];
                
                for (const deviceId of deviceIds) {
                    const deviceConfig = devicesToUse.find(d => d.id === deviceId);
                    if (!deviceConfig) {
                        console.warn(`No device config found for device ${deviceId}. Available devices:`, devicesToUse.map(d => ({id: d.id, name: d.name})));
                        continue;
                    }
                    
                    // Use the internal ID directly for Siam GPS API
                    const siamGpsVehicleId = deviceId;
                    
                    try {
                        // Call Laravel proxy endpoint with vehicle ID in URL
                        const vehicleResponse = await fetch(`${config.realtimeUrl}/${siamGpsVehicleId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                authorization: creds.Authorization
                            })
                        });
                        
                        if (vehicleResponse.ok) {
                            const responseData = await vehicleResponse.json();
                            
                            // Handle the new API response format
                            if (responseData.status === 200 && responseData.data && responseData.data.length > 0) {
                                const vehicleData = responseData.data[0]; // Get the latest data point
                                const vehicleInfo = responseData.vehicleInfo;
                                
                                // Extract coordinates
                                let lat = 0, lng = 0;
                                if (vehicleData.localtion?.coordinates) {
                                    lat = vehicleData.localtion.coordinates[1];
                                    lng = vehicleData.localtion.coordinates[0];
                                } else if (vehicleData.location?.coordinates) {
                                    lat = vehicleData.location.coordinates[1];
                                    lng = vehicleData.location.coordinates[0];
                                }
                                
                                // Transform the data to our format
                                const transformedVehicle = {
                                    device_id: deviceConfig.id, // Keep internal ID (312767)
                                    name: deviceConfig.name,
                                    latitude: lat,
                                    longitude: lng,
                                    speed: vehicleData.speed || 0,
                                    status_name: vehicleData.vehicleStatus === 'RUNNING' ? 'Moving' : 'Stop',
                                    online: 1, // Assume online if we got data
                                    event_stamp: vehicleData.time || new Date().toISOString(),
                                    address: vehicleData.geoLocation?.sGeo || 'ไม่ระบุตำแหน่ง',
                                    fuel_liters: '-',
                                    ignition: vehicleData.vehicleStatus === 'RUNNING' ? 1 : 0,
                                    satellites: vehicleData.gpsFix ? 10 : 0,
                                    // Additional Siam GPS specific data
                                    mileage: vehicleData.mileage,
                                    heading: vehicleData.heading,
                                    battery: vehicleData.battery,
                                    vehicleStatus: vehicleData.vehicleStatus,
                                    plateNumber: vehicleInfo?._vehiPlateNo
                                };
                                allVehicleData.push(transformedVehicle);
                            }
                        }
                    } catch (vehicleError) {
                        console.error(`Error fetching vehicle ${siamGpsVehicleId}:`, vehicleError);
                    }
                }
                
                return allVehicleData;
            }

            // For other providers (like Andaman)
            const response = await fetch(config.realtimeUrl, {
                method: 'GET',
                headers
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            let data = await response.json();

            // Transform response if provider has custom format
            if (config.transformResponse) {
                data = config.transformResponse(data);
            }

            // Filter for requested device IDs
            if (provider === 'andaman') {
                return data.filter((vehicle: any) => deviceIds.includes(vehicle.device_id));
            }

            return data;
        } catch (error) {
            console.error(`Error fetching from ${provider}:`, error);
            return [];
        }
    };

    // Fetch route history from appropriate provider
    const fetchRouteDataForProvider = async (provider: string, deviceId: number, date: string) => {
        const config = API_PROVIDERS[provider];
        if (!config) {
            console.error(`Unknown provider: ${provider}`);
            return null;
        }

        try {
            const headers: any = {
                'Content-Type': 'application/json'
            };

            // Prepare credentials
            let creds = {};
            if (config.requiresAuth && providerCredentials.value[provider]) {
                creds = { ...providerCredentials.value[provider] };
                
                // For Siam GPS, ensure Authorization header has Bearer prefix
                if (provider === 'siamgps' && creds.Authorization && !creds.Authorization.startsWith('Bearer ')) {
                    creds.Authorization = `Bearer ${creds.Authorization}`;
                }
                
                // For Andaman, add headers
                if (provider === 'andaman') {
                    Object.assign(headers, creds);
                }
            }

            if (provider === 'andaman') {
                const response = await fetch(config.routeUrl, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({
                        deviceId: deviceId,
                        start: new Date(date + 'T00:00:00').getTime(),
                        end: new Date(date + 'T23:59:59').getTime()
                    })
                });

                if (!response.ok) {
                    throw new Error(`API error: ${response.status}`);
                }

                return await response.json();
            }

            // Siam GPS provider via Laravel proxy
            if (provider === 'siamgps') {
                // Use internal ID directly for Siam GPS API
                const siamGpsVehicleId = deviceId;

                // Format date range for Siam GPS API (needs ISO format with timezone)
                const startDate = new Date(date + 'T17:00:00.000Z').toISOString();
                const endDate = new Date(date + 'T16:59:59.999Z');
                endDate.setDate(endDate.getDate() + 1);
                const endDateStr = endDate.toISOString();

                const response = await fetch(config.routeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        vehicleId: siamGpsVehicleId,
                        startDate: startDate,
                        endDate: endDateStr,
                        authorization: creds.Authorization
                    })
                });

                if (!response.ok) {
                    throw new Error(`API error: ${response.status}`);
                }

                const data = await response.json();
                
                // Transform response using the provider's transform function
                if (config.transformRouteResponse) {
                    return config.transformRouteResponse(data);
                }
                
                return data;
            }

            return null;
        } catch (error) {
            console.error(`Error fetching route from ${provider}:`, error);
            return null;
        }
    };

    // Update credentials for a provider
    const updateProviderCredentials = (provider: string, credentials: Record<string, string>) => {
        providerCredentials.value[provider] = credentials;
        
        // Save to localStorage
        Object.entries(credentials).forEach(([key, value]) => {
            localStorage.setItem(`${provider}-${key.toLowerCase()}`, value);
        });
    };
    
    // Refresh credentials from localStorage
    const refreshCredentials = () => {
        const siamGpsToken = localStorage.getItem('siamgps-authorization') || '';
        // Store the raw token, Bearer prefix will be added automatically in API calls
        providerCredentials.value.siamgps.Authorization = siamGpsToken;
        providerCredentials.value.andaman.Authorization = localStorage.getItem('route-history-auth') || '';
        providerCredentials.value.andaman.Token = localStorage.getItem('route-history-token') || '';
    };

    return {
        API_PROVIDERS,
        providerCredentials,
        fetchRealtimeDataForProvider,
        fetchRouteDataForProvider,
        updateProviderCredentials,
        refreshCredentials
    };
}