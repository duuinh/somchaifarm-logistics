<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { ref, computed, watch } from 'vue';
import { PlusCircle, Pencil, Trash2, Eye, Search } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import Pagination from '@/components/Pagination.vue';

interface Item {
    id: number;
    name: string;
    regular_price_per_kg: number;
    regular_price_per_bag: number;
    credit_price_per_kg: number;
    credit_price_per_bag: number;
    kg_per_bag_conversion: number;
}

interface PaginatedData {
    current_page: number;
    data: Item[];
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
    items: PaginatedData;
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
        title: 'รายการสินค้า',
        href: '/items',
    },
];

const searchQuery = ref(props.filters?.search || '');

// Server-side search with debounce
const performSearch = useDebounceFn(() => {
    router.get('/items', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(searchQuery, () => {
    performSearch();
});


const deleteItem = (id: number) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบรายการนี้?')) {
        router.delete(`/items/${id}`);
    }
};
</script>

<template>
    <Head title="รายการสินค้า" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>จัดการรายการสินค้า</CardTitle>
                            <CardDescription>รายการสินค้าทั้งหมดในระบบ</CardDescription>
                        </div>
                        <Link :href="route('items.create')">
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                เพิ่มรายการใหม่
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
                                placeholder="ค้นหาชื่อสินค้า..."
                                class="pl-8"
                            />
                        </div>
                        <div class="text-sm text-muted-foreground">
                            พบ {{ items.total }} รายการ
                        </div>
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ชื่อ</TableHead>
                                    <TableHead>กก./กระสอบ</TableHead>
                                    <TableHead>ราคาปกติ (กก.)</TableHead>
                                    <TableHead>ราคาปกติ (กระสอบ)</TableHead>
                                    <TableHead>ราคาเครดิต (กก.)</TableHead>
                                    <TableHead>ราคาเครดิต (กระสอบ)</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="item in items.data" :key="item.id">
                                    <TableCell class="font-medium">{{ item.name }}</TableCell>
                                    <TableCell>{{ item.kg_per_bag_conversion }} กก.</TableCell>
                                    <TableCell>{{ item.regular_price_per_kg?.toLocaleString() || '-' }} บาท</TableCell>
                                    <TableCell>{{ item.regular_price_per_bag?.toLocaleString() || '-' }} บาท</TableCell>
                                    <TableCell>{{ item.credit_price_per_kg?.toLocaleString() || '-' }} บาท</TableCell>
                                    <TableCell>{{ item.credit_price_per_bag?.toLocaleString() || '-' }} บาท</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('items.show', item.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('items.edit', item.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteItem(item.id)"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="items.data.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">ไม่พบรายการสินค้าที่ตรงกับเงื่อนไขการค้นหา</p>
                    </div>

                    <!-- Pagination -->
                    <Pagination :data="items" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>