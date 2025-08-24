<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import VehicleForm from '@/components/VehicleForm.vue';

interface Vehicle {
    id: number;
    license_plate: string;
    province: string;
    vehicle_type: string;
    load_capacity: number;
    gps_device_id: number | null;
    gps_provider: string | null;
    is_active: boolean;
    color: string | null;
}

interface Props {
    vehicle: Vehicle;
}

const props = defineProps<Props>();

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
        title: `แก้ไข ${props.vehicle.license_plate}`,
        href: `/vehicles/${props.vehicle.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`แก้ไข ${vehicle.license_plate}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <VehicleForm
                :vehicle="vehicle"
                mode="edit"
                title="แก้ไขข้อมูลรถ"
                :description="`แก้ไข ${vehicle.license_plate}`"
            />
        </div>
    </AppLayout>
</template>