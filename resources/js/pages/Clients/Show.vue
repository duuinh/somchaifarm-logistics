<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

interface Client {
    id: number;
    name: string;
    address: string;
    phone: string;
    delivery_notes: Array<{
        id: number;
        delivery_date: string;
        total_amount: number;
    }>;
}

interface Props {
    client: Client;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'ลูกค้า',
        href: '/clients',
    },
    {
        title: props.client.name,
        href: `/clients/${props.client.id}`,
    },
];

</script>

<template>
    <Head :title="client.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-4xl mx-auto space-y-6">
                <!-- Client Information -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('clients.index')">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div>
                                    <CardTitle>{{ client.name }}</CardTitle>
                                    <CardDescription>ข้อมูลลูกค้า</CardDescription>
                                </div>
                            </div>
                            <Link :href="route('clients.edit', client.id)">
                                <Button>
                                    <Pencil class="mr-2 h-4 w-4" />
                                    แก้ไข
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ชื่อ</h3>
                                <p class="text-sm">{{ client.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">เบอร์โทรศัพท์</h3>
                                <p class="text-sm">{{ client.phone || '-' }}</p>
                            </div>
                            <div class="md:col-span-1">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ที่อยู่</h3>
                                <p class="text-sm">{{ client.address || '-' }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Delivery Notes History -->
                <Card>
                    <CardHeader>
                        <CardTitle>ประวัติใบส่งของ</CardTitle>
                        <CardDescription>
                            ใบส่งของทั้งหมด {{ client.delivery_notes?.length || 0 }} รายการ
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="client.delivery_notes?.length > 0" class="space-y-4">
                            <div
                                v-for="note in client.delivery_notes"
                                :key="note.id"
                                class="flex items-center justify-between p-4 border rounded-lg"
                            >
                                <div>
                                    <p class="font-medium">ใบส่งของ #{{ note.id }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        วันที่: {{ formatDate(note.delivery_date) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">{{ note.total_amount?.toLocaleString() }} บาท</p>
                                    <Link :href="route('delivery-notes.show', note.id)">
                                        <Button variant="ghost" size="sm">ดูรายละเอียด</Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-muted-foreground">ยังไม่มีใบส่งของ</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>