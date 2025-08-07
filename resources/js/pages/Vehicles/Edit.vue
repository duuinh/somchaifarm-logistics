<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Save } from 'lucide-vue-next';

interface Vehicle {
    id: number;
    license_plate: string;
    province: string;
    vehicle_type: string;
    load_capacity: number;
}

interface Props {
    vehicle: Vehicle;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'รถ',
        href: '/vehicles',
    },
    {
        title: `แก้ไข ${props.vehicle.license_plate}`,
        href: `/vehicles/${props.vehicle.id}/edit`,
    },
];

const form = useForm({
    license_plate: props.vehicle.license_plate,
    province: props.vehicle.province || '',
    vehicle_type: props.vehicle.vehicle_type || '',
    load_capacity: props.vehicle.load_capacity?.toString() || '',
});

const submit = () => {
    form.put(route('vehicles.update', props.vehicle.id));
};
</script>

<template>
    <Head :title="`แก้ไข ${vehicle.license_plate}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="max-w-2xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Link :href="route('vehicles.index')">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <CardTitle>แก้ไขข้อมูลรถ</CardTitle>
                            <CardDescription>แก้ไข {{ vehicle.license_plate }}</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="license_plate">ป้ายทะเบียน *</Label>
                            <Input
                                id="license_plate"
                                v-model="form.license_plate"
                                type="text"
                                required
                                :class="{ 'border-red-500': form.errors.license_plate }"
                            />
                            <div v-if="form.errors.license_plate" class="text-sm text-red-500">
                                {{ form.errors.license_plate }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="province">จังหวัด</Label>
                            <Input
                                id="province"
                                v-model="form.province"
                                type="text"
                                :class="{ 'border-red-500': form.errors.province }"
                            />
                            <div v-if="form.errors.province" class="text-sm text-red-500">
                                {{ form.errors.province }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="vehicle_type">ประเภทรถ</Label>
                            <Input
                                id="vehicle_type"
                                v-model="form.vehicle_type"
                                type="text"
                                placeholder="เช่น รถกระบะ, รถบรรทุก 6 ล้อ, รถบรรทุก 10 ล้อ"
                                :class="{ 'border-red-500': form.errors.vehicle_type }"
                            />
                            <div v-if="form.errors.vehicle_type" class="text-sm text-red-500">
                                {{ form.errors.vehicle_type }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="load_capacity">ความจุรับน้ำหนัก (ตัน)</Label>
                            <Input
                                id="load_capacity"
                                v-model="form.load_capacity"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': form.errors.load_capacity }"
                            />
                            <div v-if="form.errors.load_capacity" class="text-sm text-red-500">
                                {{ form.errors.load_capacity }}
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                อัปเดต
                            </Button>
                            <Link :href="route('vehicles.index')">
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