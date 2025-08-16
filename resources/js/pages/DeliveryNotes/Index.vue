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
import { PlusCircle, Pencil, Trash2, Eye, Printer, Search } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import Pagination from '@/components/Pagination.vue';

interface User {
    id: number;
    name: string;
}

interface Client {
    id: number;
    name: string;
}

interface Driver {
    id: number;
    name: string;
}

interface Vehicle {
    id: number;
    license_plate: string;
}

interface DeliveryNote {
    id: number;
    delivery_date: string;
    pricing_type: 'regular' | 'credit';
    total_weight: number;
    total_amount: number;
    cash_amount?: number;
    transfer_amount?: number;
    client: Client;
    driver?: Driver;
    vehicle?: Vehicle;
    creator: User;
    created_at: string;
}

interface PaginatedData {
    current_page: number;
    data: DeliveryNote[];
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
    deliveryNotes: PaginatedData;
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
        title: 'ใบส่งของ',
        href: '/delivery-notes',
    },
];

const searchQuery = ref(props.filters?.search || '');

// Server-side search with debounce
const performSearch = useDebounceFn(() => {
    router.get('/delivery-notes', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(searchQuery, () => {
    performSearch();
});

const getPricingTypeBadge = (type: string) => {
    const badges = {
        regular: { label: 'ปกติ', class: 'bg-blue-100 text-blue-800 hover:bg-blue-100' },
        credit: { label: 'เครดิต', class: 'bg-green-100 text-green-800 hover:bg-green-100' },
    };
    return badges[type as keyof typeof badges] || badges.regular;
};

const deleteDeliveryNote = (id: number) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบใบส่งของนี้?')) {
        router.delete(`/delivery-notes/${id}`);
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

const getPaymentStatus = (note: DeliveryNote) => {
    const totalPayments = (note.cash_amount || 0) + (note.transfer_amount || 0);
    const totalAmount = note.total_amount || 0;
    const difference = Math.abs(totalPayments - totalAmount);
    
    if (totalPayments === 0) {
        return { label: 'ยังไม่ระบุ', class: 'bg-gray-100 text-gray-800 hover:bg-gray-100' };
    } else if (difference < 0.01) {
        return { label: 'ครบถ้วน', class: 'bg-green-100 text-green-800 hover:bg-green-100' };
    } else if (totalPayments > totalAmount) {
        return { label: 'เกินจ่าย', class: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100' };
    } else {
        return { label: 'ขาด', class: 'bg-red-100 text-red-800 hover:bg-red-100' };
    }
};
</script>

<template>
    <Head title="ใบส่งของ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>จัดการใบส่งของ</CardTitle>
                            <CardDescription>รายการใบส่งของทั้งหมดในระบบ</CardDescription>
                        </div>
                        <Link :href="route('delivery-notes.create')">
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                สร้างใบส่งของใหม่
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
                                placeholder="ค้นหาเลขใบส่งของหรือชื่อลูกค้า..."
                                class="pl-8"
                            />
                        </div>
                        <div class="text-sm text-muted-foreground">
                            พบ {{ deliveryNotes.total }} รายการ
                        </div>
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>เลขที่</TableHead>
                                    <TableHead>ลูกค้า</TableHead>
                                    <TableHead>วันที่ส่ง</TableHead>
                                    <TableHead>ประเภทราคา</TableHead>
                                    <TableHead>น้ำหนัก (กก.)</TableHead>
                                    <TableHead>จำนวนเงิน</TableHead>
                                    <TableHead>สถานะการชำระ</TableHead>
                                    <TableHead>คนออกบิล</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="note in deliveryNotes.data" :key="note.id">
                                    <TableCell class="font-medium">#{{ note.id }}</TableCell>
                                    <TableCell>{{ note.client.name }}</TableCell>
                                    <TableCell>{{ formatDate(note.delivery_date) }}</TableCell>
                                    <TableCell>
                                        <Badge :class="getPricingTypeBadge(note.pricing_type).class">
                                            {{ getPricingTypeBadge(note.pricing_type).label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ note.total_weight?.toLocaleString() || '-' }}</TableCell>
                                    <TableCell>{{ note.total_amount?.toLocaleString() }} บาท</TableCell>
                                    <TableCell>
                                        <Badge :class="getPaymentStatus(note).class">
                                            {{ getPaymentStatus(note).label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ note.creator.name }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('delivery-notes.print', note.id)">
                                                <Button variant="ghost" size="sm" title="พิมพ์">
                                                    <Printer class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('delivery-notes.show', note.id)">
                                                <Button variant="ghost" size="sm" title="ดู">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('delivery-notes.edit', note.id)">
                                                <Button variant="ghost" size="sm" title="แก้ไข">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                title="ลบ"
                                                @click="deleteDeliveryNote(note.id)"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="deliveryNotes.data.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">ไม่พบใบส่งของที่ตรงกับเงื่อนไขการค้นหา</p>
                    </div>

                    <!-- Pagination -->
                    <Pagination :data="deliveryNotes" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>