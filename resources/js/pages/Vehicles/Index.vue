<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { ref, computed, watch } from 'vue';
import { PlusCircle, Pencil, Trash2, Eye, Search } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import Pagination from '@/components/Pagination.vue';

interface Vehicle {
    id: number;
    license_plate: string;
    province: string;
    vehicle_type: string;
    load_capacity: number;
}

interface PaginatedData {
    current_page: number;
    data: Vehicle[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: any[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    vehicles: PaginatedData;
    filters?: {
        search?: string;
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

const searchQuery = ref(props.filters?.search || '');

// Server-side search with debounce
const performSearch = useDebounceFn(() => {
    router.get('/vehicles', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(searchQuery, () => {
    performSearch();
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
                    <div class="mb-4 flex items-center gap-2">
                        <div class="relative max-w-sm">
                            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="ค้นหาป้ายทะเบียน, จังหวัด หรือประเภทรถ..."
                                class="pl-8"
                            />
                        </div>
                        <div class="text-sm text-muted-foreground">
                            พบ {{ vehicles.total }} รายการ
                        </div>
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
                                <TableRow v-for="vehicle in vehicles.data" :key="vehicle.id">
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

                    <div v-if="vehicles.data.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">ไม่พบรถที่ตรงกับเงื่อนไขการค้นหา</p>
                    </div>

                    <!-- Pagination -->
                    <Pagination :data="vehicles" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>