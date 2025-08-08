<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Pencil, Printer } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
}

interface Client {
    id: number;
    name: string;
    address?: string;
    phone?: string;
}

interface Driver {
    id: number;
    name: string;
    phone?: string;
}

interface Vehicle {
    id: number;
    license_plate: string;
    province?: string;
}

interface Item {
    id: number;
    name: string;
}

interface DeliveryNoteItem {
    id: number;
    quantity_kg?: number;
    quantity_bags?: number;
    quantity_units?: number;
    unit_multiplier: number;
    unit_price: number;
    total_price: number;
    item: Item;
}

interface DeliveryNote {
    id: number;
    delivery_date: string;
    pricing_type: 'regular' | 'credit';
    total_weight?: number;
    total_amount?: number;
    service_fee?: number;
    service_fee_per_ton?: number;
    notes?: string;
    client: Client;
    driver?: Driver;
    vehicle?: Vehicle;
    creator: User;
    items: DeliveryNoteItem[];
    created_at: string;
}

interface Props {
    deliveryNote: DeliveryNote;
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
    {
        title: `ใบส่งของ #${props.deliveryNote.id}`,
        href: `/delivery-notes/${props.deliveryNote.id}`,
    },
];

const getPricingTypeBadge = (type: string) => {
    const badges = {
        regular: { label: 'ราคาปกติ', class: 'bg-blue-100 text-blue-800 hover:bg-blue-100' },
        credit: { label: 'ราคาเครดิต', class: 'bg-green-100 text-green-800 hover:bg-green-100' },
    };
    return badges[type as keyof typeof badges] || badges.regular;
};


const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

const shouldShowDriverVehicle = () => {
    // Show driver/vehicle info if explicitly set
    return (props.deliveryNote.driver && props.deliveryNote.vehicle);
};
</script>

<template>
    <Head :title="`ใบส่งของ #${deliveryNote.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-4xl mx-auto space-y-6">
                <!-- Header -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('delivery-notes.index')">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div>
                                    <CardTitle>ใบส่งของ #{{ deliveryNote.id }}</CardTitle>
                                    <CardDescription>รายละเอียดใบส่งของ</CardDescription>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('delivery-notes.print', deliveryNote.id)">
                                    <Button variant="outline">
                                        <Printer class="mr-2 h-4 w-4" />
                                        พิมพ์
                                    </Button>
                                </Link>
                                <Link :href="route('delivery-notes.edit', deliveryNote.id)">
                                    <Button>
                                        <Pencil class="mr-2 h-4 w-4" />
                                        แก้ไข
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>ข้อมูลพื้นฐาน</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">วันที่ส่ง</h3>
                                <p class="text-sm">{{ formatDate(deliveryNote.delivery_date) }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ประเภทราคา</h3>
                                <Badge :class="getPricingTypeBadge(deliveryNote.pricing_type).class">
                                    {{ getPricingTypeBadge(deliveryNote.pricing_type).label }}
                                </Badge>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">น้ำหนักรวม</h3>
                                <p class="text-sm">{{ deliveryNote.total_weight?.toLocaleString() || '-' }} กิโลกรัม</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">จำนวนเงินรวม</h3>
                                <p class="text-lg font-semibold text-primary">{{ deliveryNote.total_amount?.toLocaleString() || '0' }} บาท</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">คนออกบิล</h3>
                                <p class="text-sm">{{ deliveryNote.creator.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">วันที่สร้าง</h3>
                                <p class="text-sm">{{ formatDate(deliveryNote.created_at) }}</p>
                            </div>
                        </div>
                        <div v-if="deliveryNote.notes">
                            <h3 class="text-sm font-medium text-muted-foreground mb-1">หมายเหตุ</h3>
                            <p class="text-sm">{{ deliveryNote.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Client Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>ข้อมูลลูกค้า</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ชื่อลูกค้า</h3>
                                <p class="text-sm">{{ deliveryNote.client.name }}</p>
                            </div>
                            <div v-if="deliveryNote.client.phone">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">เบอร์โทรศัพท์</h3>
                                <p class="text-sm">{{ deliveryNote.client.phone }}</p>
                            </div>
                            <div v-if="deliveryNote.client.address" class="md:col-span-2">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ที่อยู่</h3>
                                <p class="text-sm">{{ deliveryNote.client.address }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Driver and Vehicle Information (conditional) -->
                <Card v-if="shouldShowDriverVehicle()">
                    <CardHeader>
                        <CardTitle>ข้อมูลการขนส่ง</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-if="deliveryNote.driver">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">คนขับ</h3>
                                <p class="text-sm">{{ deliveryNote.driver.name }}</p>
                                <p v-if="deliveryNote.driver.phone" class="text-sm text-muted-foreground">{{ deliveryNote.driver.phone }}</p>
                            </div>
                            <div v-if="deliveryNote.vehicle">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">รถ</h3>
                                <p class="text-sm">{{ deliveryNote.vehicle.license_plate }}</p>
                                <p v-if="deliveryNote.vehicle.province" class="text-sm text-muted-foreground">{{ deliveryNote.vehicle.province }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Items -->
                <Card>
                    <CardHeader>
                        <CardTitle>รายการสินค้า</CardTitle>
                        <CardDescription>
                            รายการสินค้าทั้งหมด {{ deliveryNote.items.length }} รายการ
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>รายการ</TableHead>
                                        <TableHead>จำนวน</TableHead>
                                        <TableHead class="text-right">ราคา/หน่วย</TableHead>
                                        <TableHead class="text-right">รวม (บาท)</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="item in deliveryNote.items" :key="item.id">
                                        <TableCell>
                                            <div>
                                                {{ item.item.name }}
                                                <span v-if="item.unit_multiplier > 1" class="text-muted-foreground">
                                                    (x{{ item.unit_multiplier }})
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="space-y-1">
                                                <div v-if="item.quantity_kg">{{ item.quantity_kg }} กก.</div>
                                                <div v-if="item.quantity_bags">{{ item.quantity_bags }} ถุง</div>
                                                <div v-if="item.quantity_units">{{ item.quantity_units }} หน่วย</div>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-right">{{ item.unit_price.toLocaleString() }}</TableCell>
                                        <TableCell class="text-right font-medium">{{ item.total_price.toLocaleString() }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Summary -->
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">น้ำหนักรวมทั้งหมด</span>
                                    <span class="font-medium">{{ deliveryNote.total_weight?.toLocaleString() || '0' }} กิโลกรัม</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">รวมค่าสินค้า</span>
                                    <span>{{ deliveryNote.items.reduce((total, item) => total + item.total_price, 0).toLocaleString() }} บาท</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">ค่าผสม</span>
                                    <span>{{ deliveryNote.service_fee?.toLocaleString() || '0' }} บาท</span>
                                </div>
                                <div v-if="deliveryNote.service_fee_per_ton" class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">ค่าผสมต่อตัน</span>
                                    <span>{{ deliveryNote.service_fee_per_ton?.toLocaleString() || '0' }} บาท</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between items-center">
                                    <span class="text-sm text-muted-foreground">ยอดรวมทั้งหมด</span>
                                    <span class="text-2xl font-bold text-primary">{{ deliveryNote.total_amount?.toLocaleString() || '0' }} บาท</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>