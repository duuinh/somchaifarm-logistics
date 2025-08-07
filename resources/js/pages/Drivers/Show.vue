<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Pencil, FileText, Eye } from 'lucide-vue-next';

interface DeliveryNote {
    id: number;
    delivery_date: string;
    total_weight: number;
    total_amount: number;
    client: {
        id: number;
        name: string;
        address: string;
    };
    vehicle: {
        id: number;
        license_plate: string;
    };
}

interface Driver {
    id: number;
    name: string;
    phone: string;
    id_card_number: string;
    created_at: string;
    updated_at: string;
    delivery_notes: DeliveryNote[];
}

interface Props {
    driver: Driver;
}

const props = defineProps<Props>();

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
                                <p class="text-sm bg-gray-50 p-3 rounded-md">{{ new Date(driver.created_at).toLocaleDateString('th-TH') }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">วันที่แก้ไขล่าสุด</label>
                                <p class="text-sm bg-gray-50 p-3 rounded-md">{{ new Date(driver.updated_at).toLocaleDateString('th-TH') }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Delivery History -->
            <Card class="w-full max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        <div>
                            <CardTitle>ประวัติการส่งของ</CardTitle>
                            <CardDescription>รายการใบส่งของทั้งหมดของคนขับคนนี้</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="driver.delivery_notes && driver.delivery_notes.length > 0" class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>วันที่ส่ง</TableHead>
                                    <TableHead>ลูกค้า</TableHead>
                                    <TableHead>รถที่ใช้</TableHead>
                                    <TableHead class="text-right">น้ำหนักรวม (กก.)</TableHead>
                                    <TableHead class="text-right">ยอดรวม (บาท)</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="deliveryNote in driver.delivery_notes" :key="deliveryNote.id">
                                    <TableCell>
                                        {{ new Date(deliveryNote.delivery_date).toLocaleDateString('th-TH') }}
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ deliveryNote.client?.name || '-' }}
                                    </TableCell>
                                    <TableCell>
                                        {{ deliveryNote.vehicle?.license_plate || '-' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ deliveryNote.total_weight?.toLocaleString() || '-' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ deliveryNote.total_amount?.toLocaleString() || '-' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link :href="route('delivery-notes.show', deliveryNote.id)">
                                            <Button variant="ghost" size="sm">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 mb-4 opacity-50" />
                        <p>ยังไม่มีประวัติการส่งของ</p>
                        <p class="text-sm mb-4">คนขับคนนี้ยังไม่เคยทำการส่งของ</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>