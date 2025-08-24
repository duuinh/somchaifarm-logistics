import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Get locations from Inertia page props
const page = usePage();
const locations = computed(() => page.props.locations || []);

// Computed properties for different location types
export const officeCoordinates = computed(() => {
    const office = locations.value.find(loc => loc.type === 'office');
    if (!office) {
        throw new Error('Office location not found in database');
    }
    return { lat: parseFloat(office.latitude), lng: parseFloat(office.longitude) };
});

export const pickupLocations = computed(() => 
    locations.value
        .filter(loc => loc.type === 'pickup')
        .map(loc => ({
            lat: parseFloat(loc.latitude),
            lng: parseFloat(loc.longitude),
            name: loc.name
        }))
);

export const deliveryLocations = computed(() => 
    locations.value
        .filter(loc => loc.type === 'delivery')
        .map(loc => ({
            lat: parseFloat(loc.latitude),
            lng: parseFloat(loc.longitude),
            name: loc.name
        }))
);

export const officeLocations = computed(() => 
    locations.value
        .filter(loc => loc.type === 'office')
        .map(loc => ({
            lat: parseFloat(loc.latitude),
            lng: parseFloat(loc.longitude),
            name: loc.name
        }))
);

export const serviceLocations = computed(() => 
    locations.value
        .filter(loc => loc.type === 'service')
        .map(loc => ({
            lat: parseFloat(loc.latitude),
            lng: parseFloat(loc.longitude),
            name: loc.name
        }))
);

// Distance and radius constants
export const EARTH_RADIUS_METERS = 6371000;
export const LOCATION_RADIUS = 200; // meters - used for office detection, clustering, and route analysis

// Calculate distance between two coordinates using Haversine formula
export const calculateDistance = (lat1: number, lng1: number, lat2: number, lng2: number): number => {
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return EARTH_RADIUS_METERS * c;
};

// Composable for route filtering logic
export const useRouteFiltering = () => {
    return {
        calculateDistance,
        officeCoordinates,
        pickupLocations,
        deliveryLocations,
        serviceLocations,
        locations,
        LOCATION_RADIUS
    };
};