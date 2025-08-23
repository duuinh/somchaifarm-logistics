import { ref, computed } from 'vue';

// Device list with IDs, names, types, shortnames, and API provider
const devices = [
    { id: 46397, name: '🚚 รถกระบะ บธ-4575 พัทลุง', type: 'รถกระบะ', category: 'pickup', shortname: 'บธ-4575', provider: 'andaman' },
    { id: 62855, name: '🚚 รถกระบะ บธ-9515 พัทลุง', type: 'รถกระบะ', category: 'pickup', shortname: 'บธ-9515', provider: 'andaman' },
    { id: 312767, name: '🚚 รถกระบะ บบ-3677', type: 'รถกระบะ', category: 'pickup', shortname: 'บบ-3677', provider: 'siamgps' },
    { id: 26830, name: '🚛 รถพ่วง 80-7224/80-7225 พัทลุง', type: 'รถพ่วง', category: 'trailer', shortname: '80-7224/25', provider: 'andaman' },
    { id: 26838, name: '🚛 รถพ่วง 80-7556/80-7558 พัทลุง', type: 'รถพ่วง', category: 'trailer', shortname: '80-7556/58', provider: 'andaman' },
    { id: 46222, name: '🚛 รถพ่วง 81-1039/81-1040 พัทลุง', type: 'รถพ่วง', category: 'trailer', shortname: '81-1039/40', provider: 'andaman' },
    { id: 68221, name: '🚛 รถพ่วง 81-1041/81-1042 พัทลุง', type: 'รถพ่วง', category: 'trailer', shortname: '81-1041/42', provider: 'andaman' },
    { id: 26829, name: '🚛 รถ 10 ล้อไม้ดั้ม 80-2843 พัทลุง', type: '10 ล้อ', category: 'truck', shortname: '80-2843', provider: 'andaman' },
    { id: 26852, name: '🚛 รถ 10 ล้อดั้ม 80-5757 พัทลุง', type: '10 ล้อ', category: 'truck', shortname: '80-5757', provider: 'andaman' },
    { id: 26843, name: '🚛 รถ 10 ล้อดั้ม 80-7895 พัทลุง', type: '10 ล้อ', category: 'truck', shortname: '80-7895', provider: 'andaman' },
    { id: 26833, name: '🚚 รถ 6 ล้อ 80-7554 พัทลุง', type: '6 ล้อ', category: 'truck', shortname: '80-7554', provider: 'andaman' },
];

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

export function useVehicleConfig() {
    // Configuration state
    const routeAnalysisRadius = ref(200);
    const officeHourStart = ref(8);   // 8:00 AM
    const officeHourEnd = ref(17);    // 5:00 PM (17:00)

    // Group devices by type for better organization
    const devicesByType = computed(() => {
        const grouped = devices.reduce((acc, device) => {
            if (!acc[device.type]) {
                acc[device.type] = [];
            }
            acc[device.type].push(device);
            return acc;
        }, {} as Record<string, typeof devices>);
        
        // Define order of vehicle types
        const typeOrder = ['รถกระบะ', 'รถพ่วง', '10 ล้อ', '6 ล้อ'];
        
        const orderedGroups: Array<{ type: string; vehicles: typeof devices }> = [];
        typeOrder.forEach(type => {
            if (grouped[type]) {
                orderedGroups.push({
                    type,
                    vehicles: grouped[type].sort((a, b) => a.name.localeCompare(b.name))
                });
            }
        });
        
        return orderedGroups;
    });

    // Helper function to get vehicle color
    const getVehicleColor = (selectedDeviceIds: number[], deviceId: number) => {
        const index = selectedDeviceIds.indexOf(deviceId);
        return index >= 0 ? vehicleColors[index % vehicleColors.length] : '#0000FF';
    };

    // Set default date (today)
    const getDefaultDate = () => {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    return {
        devices,
        vehicleColors,
        devicesByType,
        routeAnalysisRadius,
        officeHourStart,
        officeHourEnd,
        getVehicleColor,
        getDefaultDate
    };
}