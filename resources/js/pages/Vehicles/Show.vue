<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

interface Vehicle {
    id: number;
    license_plate: string;
    province: string;
    vehicle_type: string;
    load_capacity: number;
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

            </div>
        </div>
    </AppLayout>
</template>