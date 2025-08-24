<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Pencil, FileText, Eye } from 'lucide-vue-next';

interface Driver {
    id: number;
    name: string;
    phone: string;
    id_card_number: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    driver: Driver;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'คนขับ',
        href: '/drivers',
    },
    {
        title: props.driver.name,
        href: `/drivers/${props.driver.id}`,
    },
];
</script>

<template>
    <Head :title="`คนขับ - ${driver.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="w-full max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Link :href="route('drivers.index')">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                        </Link>
                        <div class="flex-1">
                            <CardTitle>รายละเอียดคนขับ</CardTitle>
                            <CardDescription>ข้อมูลคนขับ {{ driver.name }}</CardDescription>
                        </div>
                        <Link :href="route('drivers.edit', driver.id)">
                            <Button>
                                <Pencil class="mr-2 h-4 w-4" />
                                แก้ไข
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">ชื่อ</label>
                                <p class="text-sm bg-gray-50 p-3 rounded-md">{{ driver.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">เบอร์โทรศัพท์</label>
                                <p class="text-sm bg-gray-50 p-3 rounded-md">{{ driver.phone || '-' }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">เลขบัตรประชาชน</label>
                            <p class="text-sm bg-gray-50 p-3 rounded-md">{{ driver.id_card_number || '-' }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">วันที่สร้าง</label>
                                <p class="text-sm bg-gray-50 p-3 rounded-md">{{ formatDate(driver.created_at) }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">วันที่แก้ไขล่าสุด</label>
                                <p class="text-sm bg-gray-50 p-3 rounded-md">{{ formatDate(driver.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>