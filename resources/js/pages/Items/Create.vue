<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Save } from 'lucide-vue-next';

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
        title: 'เพิ่มรายการใหม่',
        href: '/items/create',
    },
];

const form = useForm({
    name: '',
    regular_price_per_kg: '',
    regular_price_per_bag: '',
    credit_price_per_kg: '',
    credit_price_per_bag: '',
    kg_per_bag_conversion: '25',
});

const submit = () => {
    form.post(route('items.store'));
};
</script>

<template>
    <Head title="เพิ่มรายการใหม่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="w-full max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Link :href="route('items.index')">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <CardTitle>เพิ่มรายการใหม่</CardTitle>
                            <CardDescription>เพิ่มสินค้าใหม่</CardDescription>
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
                                <Label for="regular_price_per_kg">ราคาปกติ ต่อ กก. (บาท)</Label>
                                <Input
                                    id="regular_price_per_kg"
                                    v-model="form.regular_price_per_kg"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="regular_price_per_bag">ราคาปกติ ต่อ กระสอบ (บาท)</Label>
                                <Input
                                    id="regular_price_per_bag"
                                    v-model="form.regular_price_per_bag"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="credit_price_per_kg">ราคาเครดิต ต่อ กก. (บาท)</Label>
                                <Input
                                    id="credit_price_per_kg"
                                    v-model="form.credit_price_per_kg"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="credit_price_per_bag">ราคาเครดิต ต่อ กระสอบ (บาท)</Label>
                                <Input
                                    id="credit_price_per_bag"
                                    v-model="form.credit_price_per_bag"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
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

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                บันทึก
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