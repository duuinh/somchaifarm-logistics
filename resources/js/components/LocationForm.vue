<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Save, MapPin } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

interface Location {
    id?: number;
    type: 'office' | 'pickup' | 'delivery' | 'service';
    name: string;
    latitude: number | string;
    longitude: number | string;
    is_active: boolean;
}

interface Props {
    location?: Location;
    mode: 'create' | 'edit';
    title: string;
    description: string;
}

const props = defineProps<Props>();

const form = useForm({
    type: props.location?.type || 'office',
    name: props.location?.name || '',
    latitude: props.location ? parseFloat(props.location.latitude.toString()) : 0,
    longitude: props.location ? parseFloat(props.location.longitude.toString()) : 0,
    is_active: props.location?.is_active ?? true,
});

const mapContainer = ref<HTMLDivElement>();
const map = ref<any>(null);
const marker = ref<any>(null);

onMounted(async () => {
    if (!mapContainer.value) return;
    
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    
    const lat = props.location ? parseFloat(props.location.latitude.toString()) : 13.7563;
    const lng = props.location ? parseFloat(props.location.longitude.toString()) : 100.5018;
    const zoom = props.location ? 15 : 10;
    
    // Initialize map
    map.value = L.map(mapContainer.value).setView([lat, lng], zoom);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map.value);
    
    // Add existing marker if editing
    if (props.location) {
        marker.value = L.marker([lat, lng]).addTo(map.value);
    }
    
    // Add click handler to set/update location
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

const getTypeLabel = (type: string) => {
    const labels = {
        office: 'สำนักงาน',
        pickup: 'จุดรับสินค้า',
        delivery: 'จุดส่งสินค้า',
        service: 'จุดบริการ'
    };
    return labels[type as keyof typeof labels] || type;
};

const submit = () => {
    if (props.mode === 'create') {
        form.post(route('locations.store'));
    } else {
        form.put(route('locations.update', props.location!.id));
    }
};
</script>

<template>
    <Card class="w-full max-w-6xl mx-auto">
        <CardHeader>
            <div class="flex items-center gap-4">
                <Link :href="route('locations.index')">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <CardTitle>{{ title }}</CardTitle>
                    <CardDescription>{{ description }}</CardDescription>
                </div>
            </div>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Form Fields -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="type">ประเภทสถานที่ *</Label>
                            <select
                                id="type"
                                v-model="form.type"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.type }"
                            >
                                <option value="office">สำนักงาน</option>
                                <option value="pickup">จุดรับสินค้า</option>
                                <option value="delivery">จุดส่งสินค้า</option>
                                <option value="service">จุดบริการ</option>
                            </select>
                            <div v-if="form.errors.type" class="text-sm text-red-500">
                                {{ form.errors.type }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">ชื่อสถานที่ *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="เช่น สำนักงานใหญ่, คลังสินค้า 1"
                                required
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <div v-if="form.errors.name" class="text-sm text-red-500">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
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
                                <div v-if="form.errors.latitude" class="text-sm text-red-500">
                                    {{ form.errors.latitude }}
                                </div>
                            </div>
                            <div class="space-y-2">
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
                                <div v-if="form.errors.longitude" class="text-sm text-red-500">
                                    {{ form.errors.longitude }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Section -->
                        <div class="border-t pt-6">
                            <div class="flex items-center gap-3">
                                <Checkbox
                                    id="is_active"
                                    v-model:checked="form.is_active"
                                />
                                <div class="space-y-1">
                                    <Label for="is_active" class="cursor-pointer">สถานะการใช้งาน</Label>
                                    <p class="text-sm text-muted-foreground">เปิด/ปิดการใช้งานสถานที่นี้ในระบบ</p>
                                </div>
                            </div>
                            <div v-if="form.errors.is_active" class="text-sm text-red-500 mt-2">
                                {{ form.errors.is_active }}
                            </div>
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="space-y-4">
                        <div>
                            <Label>เลือกตำแหน่งบนแผนที่</Label>
                            <div class="mt-2 text-sm text-muted-foreground mb-2 flex items-center gap-2">
                                <MapPin class="h-4 w-4" />
                                {{ mode === 'create' ? 'คลิกบนแผนที่เพื่อเลือกตำแหน่ง' : 'คลิกบนแผนที่เพื่อเปลี่ยนตำแหน่ง' }}
                            </div>
                            <div ref="mapContainer" class="h-96 rounded-md border shadow-sm"></div>
                        </div>
                        
                        <!-- Coordinates Display -->
                        <div class="p-3 bg-muted rounded-lg">
                            <div class="text-sm font-medium text-muted-foreground mb-2">พิกัดที่เลือก</div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="font-medium">ละติจูด:</span>
                                    <span class="ml-1">{{ form.latitude || '-' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">ลองจิจูด:</span>
                                    <span class="ml-1">{{ form.longitude || '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ mode === 'create' ? 'บันทึก' : 'อัปเดต' }}
                    </Button>
                    <Link :href="route('locations.index')">
                        <Button variant="outline" type="button">
                            ยกเลิก
                        </Button>
                    </Link>
                </div>
            </form>
        </CardContent>
    </Card>
</template>