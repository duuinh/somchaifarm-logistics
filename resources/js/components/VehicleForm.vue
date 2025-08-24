<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Save } from 'lucide-vue-next';

interface Vehicle {
    id?: number;
    license_plate: string;
    province: string | null;
    vehicle_type: string | null;
    load_capacity: number | null;
    gps_device_id: number | null;
    gps_provider: string | null;
    is_active: boolean;
    color: string | null;
}

interface Props {
    vehicle?: Vehicle;
    mode: 'create' | 'edit';
    title: string;
    description: string;
}

const props = defineProps<Props>();

const form = useForm({
    license_plate: props.vehicle?.license_plate || '',
    province: props.vehicle?.province || '',
    vehicle_type: props.vehicle?.vehicle_type || '',
    load_capacity: props.vehicle?.load_capacity?.toString() || '',
    gps_device_id: props.vehicle?.gps_device_id?.toString() || '',
    gps_provider: props.vehicle?.gps_provider || '',
    is_active: props.vehicle?.is_active ?? true,
    color: props.vehicle?.color || '#0000FF',
});

const submit = () => {
    if (props.mode === 'create') {
        form.post(route('vehicles.store'));
    } else {
        form.put(route('vehicles.update', props.vehicle!.id));
    }
};
</script>

<template>
    <Card class="w-full max-w-4xl mx-auto">
        <CardHeader>
            <div class="flex items-center gap-4">
                <Link :href="route('vehicles.index')">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <CardTitle>{{ title }}</CardTitle>
                    <CardDescription>{{ description }}</CardDescription>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <div class="space-y-2">
                        <Label for="color">สีรถ</Label>
                        <div class="flex gap-2">
                            <Input
                                id="color"
                                v-model="form.color"
                                type="color"
                                class="w-16 h-10 p-1 border rounded"
                                :class="{ 'border-red-500': form.errors.color }"
                            />
                            <Input
                                v-model="form.color"
                                type="text"
                                class="flex-1"
                                placeholder="#0000FF"
                                :class="{ 'border-red-500': form.errors.color }"
                            />
                        </div>
                        <div v-if="form.errors.color" class="text-sm text-red-500">
                            {{ form.errors.color }}
                        </div>
                    </div>
                </div>

                <!-- GPS Configuration Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium mb-4">การตั้งค่า GPS</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="gps_device_id">Device ID (GPS)</Label>
                            <Input
                                id="gps_device_id"
                                v-model="form.gps_device_id"
                                type="number"
                                min="1"
                                placeholder="หมายเลขอุปกรณ์ GPS"
                                :class="{ 'border-red-500': form.errors.gps_device_id }"
                            />
                            <div v-if="form.errors.gps_device_id" class="text-sm text-red-500">
                                {{ form.errors.gps_device_id }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="gps_provider">ผู้ให้บริการ GPS</Label>
                            <select 
                                id="gps_provider"
                                v-model="form.gps_provider"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.gps_provider }"
                            >
                                <option value="">ไม่ระบุ</option>
                                <option value="andaman">Andaman Tracking</option>
                                <option value="siamgps">Siam GPS</option>
                            </select>
                            <div v-if="form.errors.gps_provider" class="text-sm text-red-500">
                                {{ form.errors.gps_provider }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="border-t pt-6">
                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="is_active"
                            v-model:checked="form.is_active"
                        />
                        <div class="space-y-1">
                            <Label for="is_active" class="cursor-pointer">สถานะการใช้งาน</Label>
                            <p class="text-sm text-muted-foreground">เปิด/ปิดการใช้งานรถคันนี้ในระบบ</p>
                        </div>
                    </div>
                    <div v-if="form.errors.is_active" class="text-sm text-red-500 mt-2">
                        {{ form.errors.is_active }}
                    </div>
                </div>

                <div class="flex gap-4">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ mode === 'create' ? 'บันทึก' : 'อัปเดต' }}
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
</template>