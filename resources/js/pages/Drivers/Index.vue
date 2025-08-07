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

interface Driver {
    id: number;
    name: string;
    phone: string;
    id_card_number: string;
}

interface Props {
    drivers: {
        data: Driver[];
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
        title: 'คนขับ',
        href: '/drivers',
    },
];

const searchQuery = ref('');

const filteredDrivers = computed(() => {
    if (!searchQuery.value) return props.drivers.data;
    return props.drivers.data.filter(driver =>
        driver.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        driver.phone?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        driver.id_card_number?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
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
                    <div class="mb-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="ค้นหาชื่อคนขับ, เบอร์โทร หรือเลขบัตรประชาชน..."
                            class="max-w-sm"
                        />
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
                                <TableRow v-for="driver in filteredDrivers" :key="driver.id">
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>