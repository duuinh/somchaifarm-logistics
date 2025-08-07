<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';
import { PlusCircle, Pencil, Trash2, Eye } from 'lucide-vue-next';

interface Client {
    id: number;
    name: string;
    address: string;
    phone: string;
}

interface Props {
    clients: {
        data: Client[];
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
        title: 'ลูกค้า',
        href: '/clients',
    },
];

const searchQuery = ref('');

const filteredClients = computed(() => {
    if (!searchQuery.value) return props.clients.data;
    return props.clients.data.filter(client =>
        client.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        client.phone?.includes(searchQuery.value)
    );
});


const deleteClient = (id: number) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบลูกค้านี้?')) {
        router.delete(`/clients/${id}`);
    }
};
</script>

<template>
    <Head title="ลูกค้า" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>จัดการลูกค้า</CardTitle>
                            <CardDescription>รายการลูกค้าทั้งหมดในระบบ</CardDescription>
                        </div>
                        <Link :href="route('clients.create')">
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                เพิ่มลูกค้าใหม่
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="mb-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="ค้นหาชื่อลูกค้าหรือเบอร์โทร..."
                            class="max-w-sm"
                        />
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ชื่อ</TableHead>
                                    <TableHead>ที่อยู่</TableHead>
                                    <TableHead>โทรศัพท์</TableHead>
                                    <TableHead class="text-right">การดำเนินการ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="client in filteredClients" :key="client.id">
                                    <TableCell class="font-medium">{{ client.name }}</TableCell>
                                    <TableCell>{{ client.address || '-' }}</TableCell>
                                    <TableCell>{{ client.phone || '-' }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('clients.show', client.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('clients.edit', client.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteClient(client.id)"
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