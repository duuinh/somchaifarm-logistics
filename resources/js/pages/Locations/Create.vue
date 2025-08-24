<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import LocationForm from '@/components/LocationForm.vue';

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
</script>

<template>
    <Head title="เพิ่มสถานที่ใหม่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <LocationForm
                mode="create"
                title="เพิ่มสถานที่ใหม่"
                description="กรอกข้อมูลสถานที่และจุดสำคัญ"
            />
        </div>
    </AppLayout>
</template>