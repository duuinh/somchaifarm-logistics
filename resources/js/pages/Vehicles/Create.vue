<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import VehicleForm from '@/components/VehicleForm.vue';

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
        title: 'เพิ่มรถใหม่',
        href: '/vehicles/create',
    },
];
</script>

<template>
    <Head title="เพิ่มรถใหม่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <VehicleForm
                mode="create"
                title="เพิ่มรถใหม่"
                description="กรอกข้อมูลรถใหม่"
            />
        </div>
    </AppLayout>
</template>