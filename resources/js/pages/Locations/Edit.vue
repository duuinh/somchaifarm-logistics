<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import LocationForm from '@/components/LocationForm.vue';

interface Location {
    id: number;
    type: 'office' | 'pickup' | 'delivery' | 'service';
    name: string;
    latitude: number | string;
    longitude: number | string;
    is_active: boolean;
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
        title: 'แก้ไขสถานที่',
    },
];
</script>

<template>
    <Head title="แก้ไขสถานที่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <LocationForm
                :location="location"
                mode="edit"
                title="แก้ไขสถานที่"
                description="แก้ไขข้อมูลสถานที่และจุดสำคัญ"
            />
        </div>
    </AppLayout>
</template>