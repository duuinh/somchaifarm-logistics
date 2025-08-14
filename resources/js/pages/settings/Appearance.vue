<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'ตั้งค่าการแสดงผล',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="ตั้งค่าการแสดงผล" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="ตั้งค่าการแสดงผล" description="อัปเดตการตั้งค่าการแสดงผลของบัญชีของคุณ" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
