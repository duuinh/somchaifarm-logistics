<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ref, computed, watch } from 'vue';
import { PlusCircle, Pencil, Trash2, Eye, Search, MapPin } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import Pagination from '@/components/Pagination.vue';

interface Location {
    id: number;
    type: 'office' | 'pickup' | 'delivery' | 'service';
    name: string;
    latitude: number;
    longitude: number;
    is_active: boolean;
}

interface PaginatedData {
    current_page: number;
    data: Location[];
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
    locations: PaginatedData;
    filters?: {
        search?: string;
        type?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'จัดการสถานที่',
        href: '/locations',
    },
];

const searchQuery = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');

// Server-side search with debounce
const performSearch = useDebounceFn(() => {
    router.get('/locations', { 
        search: searchQuery.value,
        type: typeFilter.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch([searchQuery, typeFilter], () => {
    performSearch();
});

const deleteLocation = (id: number) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบสถานที่นี้?')) {
        router.delete(`/locations/${id}`);
    }
};

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        office: 'สำนักงาน',
        pickup: 'จุดรับสินค้า',
        delivery: 'จุดส่งสินค้า',
        service: 'จุดบริการ'
    };
    return labels[type] || type;
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        office: 'bg-blue-100 text-blue-800',
        pickup: 'bg-green-100 text-green-800',
        delivery: 'bg-orange-100 text-orange-800',
        service: 'bg-purple-100 text-purple-800'
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="จัดการสถานที่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>จัดการสถานที่</CardTitle>
                            <CardDescription>รายชื่อสถานที่และจุดสำคัญทั้งหมดในระบบ</CardDescription>
                        </div>
                        <Link :href="route('locations.create')">
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                เพิ่มสถานที่ใหม่
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
                                placeholder="ค้นหาชื่อสถานที่..."
                                class="pl-8"
                            />
                        </div>
                        <select 
                            v-model="typeFilter"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm"
                        >
                            <option value="">ทุกประเภท</option>
                            <option value="office">สำนักงาน</option>
                            <option value="pickup">จุดรับสินค้า</option>
                            <option value="delivery">จุดส่งสินค้า</option>
                            <option value="service">จุดบริการ</option>
                        </select>
                        <div class="text-sm text-muted-foreground">
                            พบ {{ locations.total }} รายการ
                        </div>
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ชื่อสถานที่</TableHead>
                                    <TableHead>ประเภท</TableHead>
                                    <TableHead>พิกัด</TableHead>
                                    <TableHead>สถานะ</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="location in locations.data" :key="location.id">
                                    <TableCell class="font-medium">
                                        <div class="flex items-center gap-2">
                                            <MapPin class="h-4 w-4 text-gray-400" />
                                            {{ location.name }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getTypeColor(location.type)">
                                            {{ getTypeLabel(location.type) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="font-mono text-xs">
                                        {{ parseFloat(location.latitude).toFixed(6) }}, {{ parseFloat(location.longitude).toFixed(6) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="location.is_active ? 'default' : 'secondary'">
                                            {{ location.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('locations.show', location.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('locations.edit', location.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="deleteLocation(location.id)"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="locations.data.length === 0">
                                    <TableCell colspan="5" class="text-center text-muted-foreground">
                                        ไม่พบข้อมูลสถานที่
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-if="locations.last_page > 1" class="mt-4">
                        <Pagination :data="locations" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>