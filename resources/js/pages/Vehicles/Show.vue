<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

interface DeliveryNote {
    id: number;
    delivery_date: string;
    total_amount: number;
    client: {
        id: number;
        name: string;
    };
}

interface Vehicle {
    id: number;
    license_plate: string;
    province: string;
    vehicle_type: string;
    load_capacity: number;
    delivery_notes: DeliveryNote[];
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
                <!-- Vehicle Information -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('vehicles.index')">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div>
                                    <CardTitle>{{ vehicle.license_plate }}</CardTitle>
                                    <CardDescription>ข้อมูลรถ</CardDescription>
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
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ป้ายทะเบียน</h3>
                                <p class="text-sm">{{ vehicle.license_plate }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">จังหวัด</h3>
                                <p class="text-sm">{{ vehicle.province || '-' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ประเภทรถ</h3>
                                <p class="text-sm">{{ vehicle.vehicle_type || '-' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ความจุรับน้ำหนัก</h3>
                                <p class="text-sm">{{ vehicle.load_capacity || '-' }} ตัน</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Delivery Notes History -->
                <Card>
                    <CardHeader>
                        <CardTitle>ประวัติการส่งของ</CardTitle>
                        <CardDescription>
                            ใบส่งของที่ใช้รถคันนี้ทั้งหมด {{ vehicle.delivery_notes?.length || 0 }} รายการ
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="vehicle.delivery_notes?.length > 0" class="space-y-4">
                            <div
                                v-for="note in vehicle.delivery_notes"
                                :key="note.id"
                                class="flex items-center justify-between p-4 border rounded-lg"
                            >
                                <div>
                                    <p class="font-medium">ใบส่งของ #{{ note.id }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        ลูกค้า: {{ note.client.name }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        วันที่: {{ new Date(note.delivery_date).toLocaleDateString('th-TH') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">{{ note.total_amount?.toLocaleString() }} บาท</p>
                                    <Link :href="route('delivery-notes.show', note.id)">
                                        <Button variant="ghost" size="sm">ดูรายละเอียด</Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-muted-foreground">ยังไม่มีการใช้รถคันนี้ในใบส่งของ</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>