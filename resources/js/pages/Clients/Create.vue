<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
// import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Save } from 'lucide-vue-next';

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
        title: 'เพิ่มลูกค้าใหม่',
        href: '/clients/create',
    },
];

const form = useForm({
    name: '',
    address: '',
    phone: '',
});

const submit = () => {
    form.post(route('clients.store'));
};
</script>

<template>
    <Head title="เพิ่มลูกค้าใหม่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="max-w-2xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Link :href="route('clients.index')">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <CardTitle>เพิ่มลูกค้าใหม่</CardTitle>
                            <CardDescription>กรอกข้อมูลลูกค้าใหม่</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">ชื่อลูกค้า *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <div v-if="form.errors.name" class="text-sm text-red-500">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">ที่อยู่</Label>
                            <Textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                :class="{ 'border-red-500': form.errors.address }"
                            />
                            <div v-if="form.errors.address" class="text-sm text-red-500">
                                {{ form.errors.address }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">เบอร์โทรศัพท์</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                :class="{ 'border-red-500': form.errors.phone }"
                            />
                            <div v-if="form.errors.phone" class="text-sm text-red-500">
                                {{ form.errors.phone }}
                            </div>
                        </div>


                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                บันทึก
                            </Button>
                            <Link :href="route('clients.index')">
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