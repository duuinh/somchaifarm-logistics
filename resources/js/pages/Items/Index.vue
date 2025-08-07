<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';
import { PlusCircle, Pencil, Trash2, Eye } from 'lucide-vue-next';

interface Item {
    id: number;
    name: string;
    item_type: 'material' | 'service';
    unit_type: string;
    regular_price_per_kg: number;
    regular_price_per_bag: number;
    credit_price_per_kg: number;
    credit_price_per_bag: number;
}

interface Props {
    items: {
        data: Item[];
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
        title: 'รายการสินค้า',
        href: '/items',
    },
];

const searchQuery = ref('');

const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items.data;
    return props.items.data.filter(item =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const getItemTypeBadge = (type: string) => {
    const badges = {
        material: { label: 'วัตถุดิบ', class: 'bg-blue-100 text-blue-800 hover:bg-blue-100' },
        service: { label: 'บริการ', class: 'bg-green-100 text-green-800 hover:bg-green-100' },
    };
    return badges[type as keyof typeof badges] || badges.material;
};

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
                            <CardDescription>รายการสินค้าและบริการทั้งหมดในระบบ</CardDescription>
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
                    <div class="mb-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="ค้นหาชื่อสินค้า..."
                            class="max-w-sm"
                        />
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ชื่อ</TableHead>
                                    <TableHead>ประเภท</TableHead>
                                    <TableHead>หน่วย</TableHead>
                                    <TableHead>ราคาปกติ (กก.)</TableHead>
                                    <TableHead>ราคาเครดิต (กก.)</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="item in filteredItems" :key="item.id">
                                    <TableCell class="font-medium">{{ item.name }}</TableCell>
                                    <TableCell>
                                        <Badge :class="getItemTypeBadge(item.item_type).class">
                                            {{ getItemTypeBadge(item.item_type).label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ item.unit_type }}</TableCell>
                                    <TableCell>{{ item.regular_price_per_kg?.toLocaleString() || '-' }} บาท</TableCell>
                                    <TableCell>{{ item.credit_price_per_kg?.toLocaleString() || '-' }} บาท</TableCell>
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>