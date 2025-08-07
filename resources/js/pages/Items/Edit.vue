<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Save } from 'lucide-vue-next';

interface Item {
    id: number;
    name: string;
    item_type: 'material';
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
        title: `แก้ไข ${props.item.name}`,
        href: `/items/${props.item.id}/edit`,
    },
];

const form = useForm({
    name: props.item.name,
    item_type: 'material' as 'material',
    unit_type: props.item.unit_type,
    regular_price_per_kg: props.item.regular_price_per_kg?.toString() || '',
    regular_price_per_bag: props.item.regular_price_per_bag?.toString() || '',
    regular_price_per_ton: props.item.regular_price_per_ton?.toString() || '',
    regular_price_per_unit: props.item.regular_price_per_unit?.toString() || '',
    credit_price_per_kg: props.item.credit_price_per_kg?.toString() || '',
    credit_price_per_bag: props.item.credit_price_per_bag?.toString() || '',
    credit_price_per_ton: props.item.credit_price_per_ton?.toString() || '',
    credit_price_per_unit: props.item.credit_price_per_unit?.toString() || '',
    kg_per_bag_conversion: props.item.kg_per_bag_conversion?.toString() || '',
});

const submit = () => {
    form.put(route('items.update', props.item.id));
};
</script>

<template>
    <Head :title="`แก้ไข ${item.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Link :href="route('items.index')">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <CardTitle>แก้ไขรายการสินค้า</CardTitle>
                            <CardDescription>แก้ไข {{ item.name }}</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="name">ชื่อสินค้า *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    required
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <div v-if="form.errors.name" class="text-sm text-red-500">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="item_type">ประเภท *</Label>
                                <Input
                                    id="item_type"
                                    value="วัตถุดิบ"
                                    readonly
                                    class="bg-muted"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="unit_type">หน่วย *</Label>
                                <select 
                                    id="unit_type"
                                    v-model="form.unit_type"
                                    class="flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="kg">กิโลกรัม</option>
                                    <option value="bags">กระสอบ</option>
                                    <option value="ton">ตัน</option>
                                    <option value="unit">หน่วย</option>
                                    <option value="both">ทั้งกิโลและกระสอบ</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="kg_per_bag_conversion">กิโลกรัมต่อกระสอบ</Label>
                                <Input
                                    id="kg_per_bag_conversion"
                                    v-model="form.kg_per_bag_conversion"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Pricing Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium">ราคาปกติ</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="regular_price_per_kg">ราคาต่อ กก. (บาท)</Label>
                                    <Input
                                        id="regular_price_per_kg"
                                        v-model="form.regular_price_per_kg"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="regular_price_per_bag">ราคาต่อ กระสอบ (บาท)</Label>
                                    <Input
                                        id="regular_price_per_bag"
                                        v-model="form.regular_price_per_bag"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="regular_price_per_ton">ราคาต่อ ตัน (บาท)</Label>
                                    <Input
                                        id="regular_price_per_ton"
                                        v-model="form.regular_price_per_ton"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="regular_price_per_unit">ราคาต่อ หน่วย (บาท)</Label>
                                    <Input
                                        id="regular_price_per_unit"
                                        v-model="form.regular_price_per_unit"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <h3 class="text-lg font-medium mt-6">ราคาเครดิต</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="credit_price_per_kg">ราคาต่อ กก. (บาท)</Label>
                                    <Input
                                        id="credit_price_per_kg"
                                        v-model="form.credit_price_per_kg"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="credit_price_per_bag">ราคาต่อ กระสอบ (บาท)</Label>
                                    <Input
                                        id="credit_price_per_bag"
                                        v-model="form.credit_price_per_bag"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="credit_price_per_ton">ราคาต่อ ตัน (บาท)</Label>
                                    <Input
                                        id="credit_price_per_ton"
                                        v-model="form.credit_price_per_ton"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="credit_price_per_unit">ราคาต่อ หน่วย (บาท)</Label>
                                    <Input
                                        id="credit_price_per_unit"
                                        v-model="form.credit_price_per_unit"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                อัปเดต
                            </Button>
                            <Link :href="route('items.index')">
                                <Button variant="outline" type="button">
                                    ยกเลิก
                                </Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>