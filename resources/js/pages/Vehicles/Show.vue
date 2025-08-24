<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Pencil, Truck, MapPin, Gauge, Palette, Satellite, Wifi } from 'lucide-vue-next';

interface Vehicle {
    id: number;
    license_plate: string;
    province: string | null;
    vehicle_type: string | null;
    load_capacity: number | null;
    gps_device_id: number | null;
    gps_provider: string | null;
    is_active: boolean;
    color: string | null;
    created_at?: string;
    updated_at?: string;
}

interface Props {
    vehicle: Vehicle;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

const getProviderName = (provider: string | null) => {
    switch (provider) {
        case 'andaman':
            return 'Andaman Tracking';
        case 'siamgps':
            return 'Siam GPS';
        default:
            return 'ไม่ระบุ';
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'รถ',
        href: '/vehicles',
    },
    {
        title: props.vehicle.license_plate,
        href: `/vehicles/${props.vehicle.id}`,
    },
];
</script>

<template>
    <Head :title="vehicle.license_plate" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-4xl mx-auto space-y-6">
                <!-- Header Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('vehicles.index')">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div class="flex items-center gap-3">
                                    <!-- Vehicle Color Indicator -->
                                    <div
                                        v-if="vehicle.color"
                                        class="w-6 h-6 rounded-full border-2 border-white shadow-sm"
                                        :style="{ backgroundColor: vehicle.color }"
                                    ></div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <CardTitle>{{ vehicle.license_plate }}</CardTitle>
                                            <Badge :variant="vehicle.is_active ? 'default' : 'secondary'">
                                                {{ vehicle.is_active ? 'ใช้งานอยู่' : 'ไม่ได้ใช้งาน' }}
                                            </Badge>
                                        </div>
                                        <CardDescription>ข้อมูลรถ {{ vehicle.vehicle_type || '' }}</CardDescription>
                                    </div>
                                </div>
                            </div>
                            <Link :href="route('vehicles.edit', vehicle.id)">
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
                            <Truck class="h-5 w-5" />
                            ข้อมูลพื้นฐาน
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Truck class="h-4 w-4" />
                                    ป้ายทะเบียน
                                </div>
                                <p class="text-base font-medium">{{ vehicle.license_plate }}</p>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <MapPin class="h-4 w-4" />
                                    จังหวัด
                                </div>
                                <p class="text-base">{{ vehicle.province || '-' }}</p>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Truck class="h-4 w-4" />
                                    ประเภทรถ
                                </div>
                                <p class="text-base">{{ vehicle.vehicle_type || '-' }}</p>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Gauge class="h-4 w-4" />
                                    ความจุรับน้ำหนัก
                                </div>
                                <p class="text-base">{{ vehicle.load_capacity ? `${vehicle.load_capacity} ตัน` : '-' }}</p>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Palette class="h-4 w-4" />
                                    สีรถ
                                </div>
                                <div class="flex items-center gap-2">
                                    <div
                                        v-if="vehicle.color"
                                        class="w-6 h-6 rounded border border-gray-300"
                                        :style="{ backgroundColor: vehicle.color }"
                                    ></div>
                                    <p class="text-base">{{ vehicle.color || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- GPS Configuration -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Satellite class="h-5 w-5" />
                            การตั้งค่า GPS
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Satellite class="h-4 w-4" />
                                    Device ID
                                </div>
                                <p class="text-base">{{ vehicle.gps_device_id || '-' }}</p>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Wifi class="h-4 w-4" />
                                    ผู้ให้บริการ GPS
                                </div>
                                <p class="text-base">{{ getProviderName(vehicle.gps_provider) }}</p>
                            </div>
                        </div>
                        
                        <div v-if="vehicle.gps_device_id" class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                                <Satellite class="h-4 w-4" />
                                <span class="text-sm font-medium">รถคันนี้พร้อมใช้งานระบบติดตาม GPS</span>
                            </div>
                        </div>
                        
                        <div v-else class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <div class="flex items-center gap-2 text-yellow-700 dark:text-yellow-300">
                                <Satellite class="h-4 w-4" />
                                <span class="text-sm font-medium">รถคันนี้ยังไม่ได้ตั้งค่า GPS สำหรับการติดตาม</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Timestamps -->
                <Card v-if="vehicle.created_at || vehicle.updated_at">
                    <CardHeader>
                        <CardTitle class="text-base">ประวัติการอัปเดต</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                            <div v-if="vehicle.created_at" class="space-y-1">
                                <p class="font-medium text-muted-foreground">วันที่สร้าง</p>
                                <p>{{ formatDate(vehicle.created_at) }}</p>
                            </div>
                            <div v-if="vehicle.updated_at" class="space-y-1">
                                <p class="font-medium text-muted-foreground">แก้ไขล่าสุด</p>
                                <p>{{ formatDate(vehicle.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>