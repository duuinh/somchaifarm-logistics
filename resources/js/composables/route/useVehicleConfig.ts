import { ref, computed } from 'vue';
import { useVehicleStore } from '@/stores/vehicleStore';

// Convert database vehicles to the format expected by tracking system
const useVehicleDevices = () => {
    const vehicleStore = useVehicleStore();
    
    return computed(() => {
        if (vehicleStore.loading || vehicleStore.vehicles.length === 0) {
            return [];
        }
        
        return vehicleStore.vehiclesWithGps.map(vehicle => ({
            id: vehicle.gps_device_id,
            name: `${vehicle.vehicle_type || 'รถ'} ${vehicle.license_plate}${vehicle.province ? ' ' + vehicle.province : ''}`,
            type: vehicle.vehicle_type,
            shortname: vehicle.license_plate,
            provider: vehicle.gps_provider || 'andaman',
            color: vehicle.color || '#0000FF'
        }));
    });
};


export function useVehicleConfig() {
    const vehicleStore = useVehicleStore();
    const devices = useVehicleDevices() || computed(() => []);
    
    // Configuration state
    const routeAnalysisRadius = ref(200);
    const officeHourStart = ref(8);   // 8:00 AM
    const officeHourEnd = ref(17);    // 5:00 PM (17:00)

    // Group devices by type for better organization
    const devicesByType = computed(() => {
        const grouped = devices.value.reduce((acc, device) => {
            if (!acc[device.type]) {
                acc[device.type] = [];
            }
            acc[device.type].push(device);
            return acc;
        }, {} as Record<string, any[]>);
        
        // Get all vehicle types dynamically and sort them alphabetically
        const orderedGroups: Array<{ type: string; vehicles: any[] }> = [];
        Object.keys(grouped).sort().reverse().forEach(type => {
            orderedGroups.push({
                type,
                vehicles: grouped[type].sort((a, b) => a.name.localeCompare(b.name))
            });
        });
        
        return orderedGroups;
    });

    // Helper function to get vehicle color - now uses store
    const getVehicleColor = (deviceId: number) => {
        return vehicleStore.getVehicleColor(deviceId);
    };

    // Set default date (today)
    const getDefaultDate = () => {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    // Initialize store
    vehicleStore.initialize();

    return {
        devices,
        devicesByType,
        routeAnalysisRadius,
        officeHourStart,
        officeHourEnd,
        getVehicleColor,
        getDefaultDate,
        vehicleStore
    };
}