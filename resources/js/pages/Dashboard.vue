<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { FileText } from 'lucide-vue-next';

interface DeliveryNote {
    id: number;
    delivery_date: string;
    total_amount: number;
    client: {
        name: string;
    } | null;
    driver: {
        name: string;
    } | null;
    vehicle: {
        license_plate: string;
    } | null;
}

interface Props {
    stats: {
        delivery_notes_count: number;
        clients_count: number;
        items_count: number;
        drivers_count: number;
        vehicles_count: number;
    };
    recent_delivery_notes: DeliveryNote[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="แดชบอร์ด - ระบบใบส่งของ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Welcome Section -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold mb-2">ระบบจัดการใบส่งของ</h1>
                <p class="text-muted-foreground">ระบบสร้างและจัดการใบส่งของสำหรับธุรกิจอาหารสัตว์</p>
            </div>

            <!-- Quick Actions -->
            <div class="flex justify-center mb-6">
                <Card class="hover:shadow-md transition-shadow cursor-pointer max-w-sm">
                    <Link :href="route('delivery-notes.create')" class="block">
                        <CardHeader class="text-center pb-2">
                            <div class="mx-auto mb-2 h-16 w-16 rounded-lg bg-blue-100 flex items-center justify-center">
                                <FileText class="h-8 w-8 text-blue-600" />
                            </div>
                            <CardTitle class="text-xl">สร้างใบส่งของใหม่</CardTitle>
                        </CardHeader>
                        <CardContent class="text-center pt-0">
                            <CardDescription>สร้างใบส่งของสำหรับลูกค้า</CardDescription>
                        </CardContent>
                    </Link>
                </Card>
            </div>

            <!-- Recent Delivery Notes -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>ใบส่งของล่าสุด</CardTitle>
                            <CardDescription>ใบส่งของที่สร้างล่าสุด</CardDescription>
                        </div>
                        <Link :href="route('delivery-notes.index')">
                            <Button variant="outline">ดูทั้งหมด</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="$props.recent_delivery_notes && $props.recent_delivery_notes.length > 0" class="space-y-4">
                        <div v-for="deliveryNote in $props.recent_delivery_notes" :key="deliveryNote.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium">ใบส่งของ #{{ deliveryNote.id }}</span>
                                    <span class="text-sm text-gray-500">{{ new Date(deliveryNote.delivery_date).toLocaleDateString('th-TH') }}</span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span v-if="deliveryNote.client">ลูกค้า: {{ deliveryNote.client.name }}</span>
                                    <span v-if="deliveryNote.driver" class="ml-3">คนขับ: {{ deliveryNote.driver.name }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">{{ deliveryNote.total_amount?.toLocaleString() || '0' }} บาท</div>
                                <Link :href="route('delivery-notes.show', deliveryNote.id)">
                                    <Button variant="ghost" size="sm" class="text-blue-600 hover:text-blue-800">
                                        ดูรายละเอียด
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 mb-4 opacity-50" />
                        <p>ยังไม่มีใบส่งของ</p>
                        <p class="text-sm mb-4">เริ่มต้นด้วยการสร้างใบส่งของใหม่</p>
                        <Link :href="route('delivery-notes.create')">
                            <Button>
                                <FileText class="mr-2 h-4 w-4" />
                                สร้างใบส่งของแรก
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">ใบส่งของทั้งหมด</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ $props.stats.delivery_notes_count }}</div>
                        <p class="text-xs text-muted-foreground">รายการ</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">ลูกค้าทั้งหมด</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ $props.stats.clients_count }}</div>
                        <p class="text-xs text-muted-foreground">รายการ</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">สินค้าทั้งหมด</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ $props.stats.items_count }}</div>
                        <p class="text-xs text-muted-foreground">รายการ</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
