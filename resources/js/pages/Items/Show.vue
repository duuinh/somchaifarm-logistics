<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

interface DeliveryNoteItem {
    id: number;
    delivery_note_id: number;
    quantity_kg: number;
    quantity_bags: number;
    price_per_kg: number;
    price_per_bag: number;
    total_amount: number;
    delivery_note: {
        id: number;
        delivery_date: string;
        client: {
            id: number;
            name: string;
        };
    };
}

interface Item {
    id: number;
    name: string;
    unit_type: string;
    regular_price_per_kg: number;
    regular_price_per_bag: number;
    regular_price_per_ton: number;
    regular_price_per_unit: number;
    credit_price_per_kg: number;
    credit_price_per_bag: number;
    credit_price_per_ton: number;
    credit_price_per_unit: number;
    kg_per_bag_conversion: number;
    delivery_note_items: DeliveryNoteItem[];
}

interface Props {
    item: Item;
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
    {
        title: props.item.name,
        href: `/items/${props.item.id}`,
    },
];

</script>

<template>
    <Head :title="item.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-4xl mx-auto space-y-6">
                <!-- Item Information -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('items.index')">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div>
                                    <CardTitle>{{ item.name }}</CardTitle>
                                    <CardDescription>ข้อมูลรายการสินค้า</CardDescription>
                                </div>
                            </div>
                            <Link :href="route('items.edit', item.id)">
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
                                <p class="text-sm">{{ item.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">หน่วย</h3>
                                <p class="text-sm">{{ item.unit_type }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">การแปลงหน่วย</h3>
                                <p class="text-sm">{{ item.kg_per_bag_conversion }} กก./กระสอบ</p>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="mt-6">
                            <h3 class="text-lg font-medium mb-4">ข้อมูลราคา</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-2">ราคาปกติ</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อกิโลกรัม:</span>
                                            <span class="text-sm">{{ item.regular_price_per_kg?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อกระสอบ:</span>
                                            <span class="text-sm">{{ item.regular_price_per_bag?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อตัน:</span>
                                            <span class="text-sm">{{ item.regular_price_per_ton?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อหน่วย:</span>
                                            <span class="text-sm">{{ item.regular_price_per_unit?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-2">ราคาเครดิต</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อกิโลกรัม:</span>
                                            <span class="text-sm">{{ item.credit_price_per_kg?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อกระสอบ:</span>
                                            <span class="text-sm">{{ item.credit_price_per_bag?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อตัน:</span>
                                            <span class="text-sm">{{ item.credit_price_per_ton?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm">ต่อหน่วย:</span>
                                            <span class="text-sm">{{ item.credit_price_per_unit?.toLocaleString() || '-' }} บาท</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Delivery Notes History -->
                <Card>
                    <CardHeader>
                        <CardTitle>ประวัติการส่งสินค้า</CardTitle>
                        <CardDescription>
                            ใบส่งของที่ใช้สินค้านี้ทั้งหมด {{ item.delivery_note_items?.length || 0 }} รายการ
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="item.delivery_note_items?.length > 0" class="space-y-4">
                            <div
                                v-for="deliveryItem in item.delivery_note_items"
                                :key="deliveryItem.id"
                                class="flex items-center justify-between p-4 border rounded-lg"
                            >
                                <div>
                                    <p class="font-medium">ใบส่งของ #{{ deliveryItem.delivery_note.id }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        ลูกค้า: {{ deliveryItem.delivery_note.client.name }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        วันที่: {{ new Date(deliveryItem.delivery_note.delivery_date).toLocaleDateString('th-TH') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm space-y-1">
                                        <div v-if="deliveryItem.quantity_kg">
                                            {{ deliveryItem.quantity_kg }} กก. × {{ deliveryItem.price_per_kg?.toLocaleString() }} บาท
                                        </div>
                                        <div v-if="deliveryItem.quantity_bags">
                                            {{ deliveryItem.quantity_bags }} กระสอบ × {{ deliveryItem.price_per_bag?.toLocaleString() }} บาท
                                        </div>
                                        <p class="font-medium">รวม {{ deliveryItem.total_amount?.toLocaleString() }} บาท</p>
                                    </div>
                                    <Link :href="route('delivery-notes.show', deliveryItem.delivery_note.id)">
                                        <Button variant="ghost" size="sm">ดูรายละเอียด</Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-muted-foreground">ยังไม่มีการใช้สินค้านี้ในใบส่งของ</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>