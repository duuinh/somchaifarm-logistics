<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { MapPin, Pencil, ArrowLeft, Building, Navigation, ExternalLink, Calendar, Clock } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

interface Location {
    id: number;
    type: 'office' | 'pickup' | 'delivery' | 'service';
    name: string;
    latitude: number | string;
    longitude: number | string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

interface Props {
    location: Location;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'จัดการสถานที่',
        href: '/locations',
    },
    {
        title: props.location.name,
    },
];

const mapContainer = ref<HTMLDivElement>();

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        office: 'สำนักงาน',
        pickup: 'จุดรับสินค้า',
        delivery: 'จุดส่งสินค้า',
        service: 'จุดบริการ'
    };
    return labels[type] || type;
};

const getTypeIcon = (type: string) => {
    const icons = {
        office: Building,
        pickup: Navigation,
        delivery: Navigation,
        service: Building
    };
    return icons[type as keyof typeof icons] || Building;
};

const getTypeBadgeVariant = (type: string) => {
    const variants = {
        office: 'default',
        pickup: 'secondary',
        delivery: 'outline', 
        service: 'secondary'
    };
    return variants[type as keyof typeof variants] || 'secondary';
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

onMounted(async () => {
    if (!mapContainer.value) return;
    
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    
    const lat = parseFloat(props.location.latitude.toString());
    const lng = parseFloat(props.location.longitude.toString());
    
    // Initialize map centered on location
    const map = L.map(mapContainer.value).setView([lat, lng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add marker
    L.marker([lat, lng])
        .bindPopup(`<strong>${props.location.name}</strong><br/>${getTypeLabel(props.location.type)}`)
        .addTo(map)
        .openPopup();
});
</script>

<template>
    <Head :title="location.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-6xl mx-auto space-y-6">
                <!-- Header Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('locations.index')">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div class="flex items-center gap-3">
                                    <component :is="getTypeIcon(location.type)" class="h-6 w-6 text-muted-foreground" />
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <CardTitle>{{ location.name }}</CardTitle>
                                            <Badge :variant="location.is_active ? 'default' : 'secondary'">
                                                {{ location.is_active ? 'ใช้งานอยู่' : 'ไม่ได้ใช้งาน' }}
                                            </Badge>
                                        </div>
                                        <CardDescription>{{ getTypeLabel(location.type) }}</CardDescription>
                                    </div>
                                </div>
                            </div>
                            <Link :href="route('locations.edit', location.id)">
                                <Button>
                                    <Pencil class="mr-2 h-4 w-4" />
                                    แก้ไข
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <MapPin class="h-5 w-5" />
                            ข้อมูลพื้นฐาน
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <MapPin class="h-4 w-4" />
                                    ชื่อสถานที่
                                </div>
                                <p class="text-base font-medium">{{ location.name }}</p>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <component :is="getTypeIcon(location.type)" class="h-4 w-4" />
                                    ประเภทสถานที่
                                </div>
                                <div>
                                    <Badge :variant="getTypeBadgeVariant(location.type)">
                                        {{ getTypeLabel(location.type) }}
                                    </Badge>
                                </div>
                            </div>
                            
                            <div class="space-y-2 md:col-span-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Navigation class="h-4 w-4" />
                                    พิกัดตำแหน่ง
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs text-muted-foreground mb-1">ละติจูด</div>
                                        <p class="font-mono text-base">{{ parseFloat(location.latitude.toString()).toFixed(6) }}</p>
                                    </div>
                                    <div>
                                        <div class="text-xs text-muted-foreground mb-1">ลองจิจูด</div>
                                        <p class="font-mono text-base">{{ parseFloat(location.longitude.toString()).toFixed(6) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Map and External Links -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- External Links -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <ExternalLink class="h-4 w-4" />
                                ลิงก์ภายนอก
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <a 
                                    :href="`https://www.google.com/maps?q=${location.latitude},${location.longitude}`"
                                    target="_blank"
                                    class="flex items-center gap-2 p-3 rounded-lg border hover:bg-muted/50 transition-colors"
                                >
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <ExternalLink class="h-4 w-4 text-blue-600" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium">Google Maps</div>
                                        <div class="text-xs text-muted-foreground">ดูตำแหน่งบนแผนที่</div>
                                    </div>
                                </a>
                                
                                <a 
                                    :href="`https://maps.apple.com/?q=${location.latitude},${location.longitude}`"
                                    target="_blank"
                                    class="flex items-center gap-2 p-3 rounded-lg border hover:bg-muted/50 transition-colors"
                                >
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <MapPin class="h-4 w-4 text-gray-600" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium">Apple Maps</div>
                                        <div class="text-xs text-muted-foreground">เปิดใน Apple Maps</div>
                                    </div>
                                </a>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Map -->
                    <Card class="lg:col-span-2">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <MapPin class="h-4 w-4" />
                                ตำแหน่งบนแผนที่
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div ref="mapContainer" class="h-96 rounded-md border shadow-sm"></div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Timestamps -->
                <Card v-if="location.created_at || location.updated_at">
                    <CardHeader>
                        <CardTitle class="text-base">ประวัติการอัปเดต</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                            <div v-if="location.created_at" class="space-y-1">
                                <div class="flex items-center gap-2 font-medium text-muted-foreground">
                                    <Calendar class="h-4 w-4" />
                                    วันที่สร้าง
                                </div>
                                <p>{{ formatDate(location.created_at) }}</p>
                            </div>
                            <div v-if="location.updated_at" class="space-y-1">
                                <div class="flex items-center gap-2 font-medium text-muted-foreground">
                                    <Clock class="h-4 w-4" />
                                    แก้ไขล่าสุด
                                </div>
                                <p>{{ formatDate(location.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>