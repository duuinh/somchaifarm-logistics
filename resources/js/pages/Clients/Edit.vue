<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Save } from 'lucide-vue-next';

interface Client {
    id: number;
    name: string;
    address: string;
    phone: string;
    customer_type: 'regular' | 'credit' | 'special';
}

interface Props {
    client: Client;
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
    {
        title: `แก้ไข ${props.client.name}`,
        href: `/clients/${props.client.id}/edit`,
    },
];

const form = useForm({
    name: props.client.name,
    address: props.client.address || '',
    phone: props.client.phone || '',
    customer_type: props.client.customer_type,
});

const submit = () => {
    form.put(route('clients.update', props.client.id));
};
</script>

<template>
    <Head :title="`แก้ไข ${client.name}`" />

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
                            <CardTitle>แก้ไขข้อมูลลูกค้า</CardTitle>
                            <CardDescription>แก้ไขข้อมูล {{ client.name }}</CardDescription>
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

                        <div class="space-y-2">
                            <Label for="customer_type">ประเภทลูกค้า *</Label>
                            <select 
                                id="customer_type"
                                v-model="form.customer_type"
                                class="flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.customer_type }"
                                required
                            >
                                <option value="regular">ลูกค้าปกติ</option>
                                <option value="credit">ลูกค้าเครดิต</option>
                                <option value="special">ลูกค้าพิเศษ</option>
                            </select>
                            <div v-if="form.errors.customer_type" class="text-sm text-red-500">
                                {{ form.errors.customer_type }}
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                อัปเดต
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