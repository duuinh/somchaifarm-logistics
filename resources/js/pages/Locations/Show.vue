<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { MapPin, Pencil, ArrowLeft } from 'lucide-vue-next';
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

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        office: 'bg-blue-100 text-blue-800',
        pickup: 'bg-green-100 text-green-800',
        delivery: 'bg-orange-100 text-orange-800',
        service: 'bg-purple-100 text-purple-800'
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
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
        .bindPopup(`<strong>${props.location.name}</strong>`)
        .addTo(map)
        .openPopup();
});
</script>

<template>
    <Head :title="location.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <MapPin class="h-5 w-5" />
                                {{ location.name }}
                            </CardTitle>
                            <CardDescription>รายละเอียดสถานที่</CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="route('locations.index')">
                                <Button variant="outline" size="sm">
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    กลับ
                                </Button>
                            </Link>
                            <Link :href="route('locations.edit', location.id)">
                                <Button size="sm">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    แก้ไข
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">ข้อมูลสถานที่</h3>
                                <dl class="mt-2 space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-600">ประเภท:</dt>
                                        <dd>
                                            <Badge :class="getTypeColor(location.type)">
                                                {{ getTypeLabel(location.type) }}
                                            </Badge>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-600">สถานะ:</dt>
                                        <dd>
                                            <Badge :variant="location.is_active ? 'default' : 'secondary'">
                                                {{ location.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                                            </Badge>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-600">พิกัด:</dt>
                                        <dd class="font-mono text-sm">
                                            {{ parseFloat(location.latitude.toString()).toFixed(6) }}, 
                                            {{ parseFloat(location.longitude.toString()).toFixed(6) }}
                                        </dd>
                                    </div>
                                    <div v-if="location.created_at" class="flex justify-between">
                                        <dt class="text-sm text-gray-600">สร้างเมื่อ:</dt>
                                        <dd class="text-sm">
                                            {{ new Date(location.created_at).toLocaleDateString('th-TH') }}
                                        </dd>
                                    </div>
                                    <div v-if="location.updated_at" class="flex justify-between">
                                        <dt class="text-sm text-gray-600">อัปเดตล่าสุด:</dt>
                                        <dd class="text-sm">
                                            {{ new Date(location.updated_at).toLocaleDateString('th-TH') }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">ลิงก์แผนที่</h3>
                                <a 
                                    :href="`https://www.google.com/maps?q=${location.latitude},${location.longitude}`"
                                    target="_blank"
                                    class="text-sm text-blue-600 hover:underline"
                                >
                                    ดูใน Google Maps →
                                </a>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">ตำแหน่งบนแผนที่</h3>
                            <div ref="mapContainer" class="h-96 rounded-md border"></div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>