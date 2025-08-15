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

interface Driver {
    id: number;
    name: string;
    phone: string;
    id_card_number: string;
}

interface PaginatedData {
    current_page: number;
    data: Driver[];
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
    drivers: PaginatedData;
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
        title: 'คนขับ',
        href: '/drivers',
    },
];

const searchQuery = ref(props.filters?.search || '');

// Server-side search with debounce
const performSearch = useDebounceFn(() => {
    router.get('/drivers', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(searchQuery, () => {
    performSearch();
});

const deleteDriver = (id: number) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบคนขับนี้?')) {
        router.delete(`/drivers/${id}`);
    }
};
</script>

<template>
    <Head title="คนขับ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>จัดการคนขับ</CardTitle>
                            <CardDescription>รายชื่อคนขับทั้งหมดในระบบ</CardDescription>
                        </div>
                        <Link :href="route('drivers.create')">
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                เพิ่มคนขับใหม่
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
                                placeholder="ค้นหาชื่อคนขับ, เบอร์โทร หรือเลขบัตรประชาชน..."
                                class="pl-8"
                            />
                        </div>
                        <div class="text-sm text-muted-foreground">
                            พบ {{ drivers.total }} รายการ
                        </div>
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ชื่อ</TableHead>
                                    <TableHead>เบอร์โทรศัพท์</TableHead>
                                    <TableHead>เลขบัตรประชาชน</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="driver in drivers.data" :key="driver.id">
                                    <TableCell class="font-medium">{{ driver.name }}</TableCell>
                                    <TableCell>{{ driver.phone || '-' }}</TableCell>
                                    <TableCell>{{ driver.id_card_number || '-' }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('drivers.show', driver.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('drivers.edit', driver.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteDriver(driver.id)"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="drivers.data.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">ไม่พบคนขับที่ตรงกับเงื่อนไขการค้นหา</p>
                    </div>

                    <!-- Pagination -->
                    <Pagination :data="drivers" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>