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
import { PlusCircle, Pencil, Trash2, Eye, Printer } from 'lucide-vue-next';

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
    client: Client;
    driver?: Driver;
    vehicle?: Vehicle;
    creator: User;
    created_at: string;
}

interface Props {
    deliveryNotes: {
        data: DeliveryNote[];
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
        title: 'ใบส่งของ',
        href: '/delivery-notes',
    },
];

const searchQuery = ref('');

const filteredNotes = computed(() => {
    if (!searchQuery.value) return props.deliveryNotes.data;
    return props.deliveryNotes.data.filter(note =>
        note.client.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        note.id.toString().includes(searchQuery.value)
    );
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
    return new Date(dateString).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
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
                    <div class="mb-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="ค้นหาเลขใบส่งของหรือชื่อลูกค้า..."
                            class="max-w-sm"
                        />
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
                                    <TableHead>คนออกบิล</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="note in filteredNotes" :key="note.id">
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
                    
                    <div v-if="filteredNotes.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">ไม่พบใบส่งของ</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>