<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';
import { PlusCircle, Pencil, Trash2, Eye } from 'lucide-vue-next';

interface Vehicle {
    id: number;
    license_plate: string;
    province: string;
    vehicle_type: string;
    load_capacity: number;
}

interface Props {
    vehicles: {
        data: Vehicle[];
        current_page: number;
        last_page: number;
        total: number;
    };
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
];

const searchQuery = ref('');

const filteredVehicles = computed(() => {
    if (!searchQuery.value) return props.vehicles.data;
    return props.vehicles.data.filter(vehicle =>
        vehicle.license_plate.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        vehicle.province?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        vehicle.vehicle_type?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const deleteVehicle = (id: number) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบรถคันนี้?')) {
        router.delete(`/vehicles/${id}`);
    }
};
</script>

<template>
    <Head title="รถ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>จัดการรถ</CardTitle>
                            <CardDescription>รายชื่อรถทั้งหมดในระบบ</CardDescription>
                        </div>
                        <Link :href="route('vehicles.create')">
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                เพิ่มรถใหม่
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="mb-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="ค้นหาป้ายทะเบียน, จังหวัด หรือประเภทรถ..."
                            class="max-w-sm"
                        />
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ป้ายทะเบียน</TableHead>
                                    <TableHead>จังหวัด</TableHead>
                                    <TableHead>ประเภทรถ</TableHead>
                                    <TableHead>ความจุ (ตัน)</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="vehicle in filteredVehicles" :key="vehicle.id">
                                    <TableCell class="font-medium">{{ vehicle.license_plate }}</TableCell>
                                    <TableCell>{{ vehicle.province || '-' }}</TableCell>
                                    <TableCell>{{ vehicle.vehicle_type || '-' }}</TableCell>
                                    <TableCell>{{ vehicle.load_capacity || '-' }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('vehicles.show', vehicle.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('vehicles.edit', vehicle.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteVehicle(vehicle.id)"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>