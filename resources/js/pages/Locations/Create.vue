<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { MapPin } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

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
        title: 'เพิ่มสถานที่ใหม่',
    },
];

const form = useForm({
    type: 'office',
    name: '',
    latitude: 0,
    longitude: 0,
    is_active: true,
});

const mapContainer = ref<HTMLDivElement>();
const map = ref<any>(null);
const marker = ref<any>(null);

onMounted(async () => {
    if (!mapContainer.value) return;
    
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    
    // Initialize map centered on Thailand
    map.value = L.map(mapContainer.value).setView([13.7563, 100.5018], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map.value);
    
    // Add click handler to set location
    map.value.on('click', (e: any) => {
        const { lat, lng } = e.latlng;
        
        // Update form
        form.latitude = lat;
        form.longitude = lng;
        
        // Update or create marker
        if (marker.value) {
            marker.value.setLatLng([lat, lng]);
        } else {
            marker.value = L.marker([lat, lng]).addTo(map.value);
        }
    });
});

const updateMapFromCoordinates = () => {
    if (!map.value) return;
    
    const lat = parseFloat(form.latitude.toString());
    const lng = parseFloat(form.longitude.toString());
    
    if (isNaN(lat) || isNaN(lng)) return;
    
    // Center map on coordinates
    map.value.setView([lat, lng], 15);
    
    // Update or create marker
    import('leaflet').then(L => {
        if (marker.value) {
            marker.value.setLatLng([lat, lng]);
        } else {
            marker.value = L.default.marker([lat, lng]).addTo(map.value);
        }
    });
};

const submit = () => {
    form.post(route('locations.store'));
};
</script>

<template>
    <Head title="เพิ่มสถานที่ใหม่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>เพิ่มสถานที่ใหม่</CardTitle>
                    <CardDescription>กรอกข้อมูลสถานที่และจุดสำคัญ</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <div>
                                    <Label for="type">ประเภทสถานที่</Label>
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                                        :class="{ 'border-red-500': form.errors.type }"
                                    >
                                        <option value="office">สำนักงาน</option>
                                        <option value="pickup">จุดรับสินค้า</option>
                                        <option value="delivery">จุดส่งสินค้า</option>
                                        <option value="service">จุดบริการ</option>
                                    </select>
                                    <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.type }}
                                    </p>
                                </div>

                                <div>
                                    <Label for="name">ชื่อสถานที่</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="เช่น สำนักงานใหญ่, คลังสินค้า 1"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label for="latitude">ละติจูด</Label>
                                        <Input
                                            id="latitude"
                                            v-model="form.latitude"
                                            type="number"
                                            step="0.000001"
                                            placeholder="13.756331"
                                            :class="{ 'border-red-500': form.errors.latitude }"
                                            @change="updateMapFromCoordinates"
                                        />
                                        <p v-if="form.errors.latitude" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.latitude }}
                                        </p>
                                    </div>
                                    <div>
                                        <Label for="longitude">ลองจิจูด</Label>
                                        <Input
                                            id="longitude"
                                            v-model="form.longitude"
                                            type="number"
                                            step="0.000001"
                                            placeholder="100.501765"
                                            :class="{ 'border-red-500': form.errors.longitude }"
                                            @change="updateMapFromCoordinates"
                                        />
                                        <p v-if="form.errors.longitude" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.longitude }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        v-model:checked="form.is_active"
                                    />
                                    <Label for="is_active">เปิดใช้งาน</Label>
                                </div>
                            </div>

                            <div>
                                <Label>เลือกตำแหน่งบนแผนที่</Label>
                                <div class="mt-2 text-sm text-gray-500 mb-2">
                                    <MapPin class="inline h-4 w-4" />
                                    คลิกบนแผนที่เพื่อเลือกตำแหน่ง
                                </div>
                                <div ref="mapContainer" class="h-96 rounded-md border"></div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <Button type="button" variant="outline" @click="$inertia.visit('/locations')">
                                ยกเลิก
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'กำลังบันทึก...' : 'บันทึก' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>