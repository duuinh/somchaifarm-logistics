<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Save } from 'lucide-vue-next';

interface Setting {
    id: number;
    key: string;
    value: string;
    type: 'string' | 'number' | 'boolean';
    description: string;
}

interface Props {
    settings: Setting[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'การตั้งค่าธุรกิจ',
        href: '/business-settings',
    },
];

const form = useForm({
    settings: props.settings.map(setting => ({
        key: setting.key,
        value: setting.type === 'number' ? parseFloat(setting.value) : setting.value,
        type: setting.type,
    }))
});

const submit = () => {
    form.put(route('business-settings.update'));
};

const getLabel = (key: string) => {
    const labels: Record<string, string> = {
        'default_service_fee': 'ค่าผสมอาหารต่อตัน (บาท)',
        'company_name': 'ชื่อบริษัท',
        'company_address': 'ที่อยู่บริษัท',
        'company_phone': 'เบอร์โทรศัพท์บริษัท',
        'company_tax_id': 'เลขประจำตัวผู้เสียภาษีอากร',
    };
    return labels[key] || key;
};
</script>

<template>
    <Head title="การตั้งค่าระบบ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="w-full max-w-4xl mx-auto">
                <CardHeader>
                    <CardTitle class="text-2xl font-bold">การตั้งค่าธุรกิจ</CardTitle>
                    <CardDescription class="text-gray-600">จัดการการตั้งค่าต่าง ๆ ของธุรกิจ</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Company Information Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold border-b pb-2">ข้อมูลบริษัท</h3>
                            <div class="grid gap-4 md:grid-cols-2">
                                <!-- Company Name -->
                                <template v-for="(setting, index) in form.settings" :key="setting.key">
                                    <div v-if="setting.key === 'company_name'" class="space-y-2">
                                        <Label :for="setting.key" class="text-sm font-medium">
                                            {{ getLabel(setting.key) }}
                                        </Label>
                                        <Input
                                            :id="setting.key"
                                            v-model="form.settings[index].value"
                                            type="text"
                                            class="w-full"
                                            :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                                        />
                                        <div v-if="form.errors[`settings.${index}.value`]" class="text-sm text-red-500">
                                            {{ form.errors[`settings.${index}.value`] }}
                                        </div>
                                    </div>
                                </template>

                                <!-- Company Phone -->
                                <template v-for="(setting, index) in form.settings" :key="setting.key">
                                    <div v-if="setting.key === 'company_phone'" class="space-y-2">
                                        <Label :for="setting.key" class="text-sm font-medium">
                                            {{ getLabel(setting.key) }}
                                        </Label>
                                        <Input
                                            :id="setting.key"
                                            v-model="form.settings[index].value"
                                            type="text"
                                            class="w-full"
                                            :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                                        />
                                        <div v-if="form.errors[`settings.${index}.value`]" class="text-sm text-red-500">
                                            {{ form.errors[`settings.${index}.value`] }}
                                        </div>
                                    </div>
                                </template>

                                <!-- Company Tax ID -->
                                <template v-for="(setting, index) in form.settings" :key="setting.key">
                                    <div v-if="setting.key === 'company_tax_id'" class="space-y-2">
                                        <Label :for="setting.key" class="text-sm font-medium">
                                            {{ getLabel(setting.key) }}
                                        </Label>
                                        <Input
                                            :id="setting.key"
                                            v-model="form.settings[index].value"
                                            type="text"
                                            class="w-full"
                                            :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                                        />
                                        <div v-if="form.errors[`settings.${index}.value`]" class="text-sm text-red-500">
                                            {{ form.errors[`settings.${index}.value`] }}
                                        </div>
                                    </div>
                                </template>

                                <!-- Company Address (spans full width) -->
                                <template v-for="(setting, index) in form.settings" :key="setting.key">
                                    <div v-if="setting.key === 'company_address'" class="col-span-full space-y-2">
                                        <Label :for="setting.key" class="text-sm font-medium">
                                            {{ getLabel(setting.key) }}
                                        </Label>
                                        <Textarea
                                            :id="setting.key"
                                            v-model="form.settings[index].value"
                                            rows="3"
                                            class="w-full"
                                            :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                                        />
                                        <div v-if="form.errors[`settings.${index}.value`]" class="text-sm text-red-500">
                                            {{ form.errors[`settings.${index}.value`] }}
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Business Settings Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold border-b pb-2">การตั้งค่าเริ่มต้น</h3>
                            <div class="grid gap-4 md:grid-cols-2">
                                <template v-for="(setting, index) in form.settings" :key="setting.key">
                                    <div v-if="!setting.key.startsWith('company_')" class="space-y-2">
                                        <Label :for="setting.key" class="text-sm font-medium">
                                            {{ getLabel(setting.key) }}
                                        </Label>

                                        <Input
                                            v-if="setting.type === 'number'"
                                            :id="setting.key"
                                            v-model.number="form.settings[index].value"
                                            type="number"
                                            step="0.01"
                                            class="w-full"
                                            :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                                        />

                                        <Input
                                            v-else-if="setting.type === 'string'"
                                            :id="setting.key"
                                            v-model="form.settings[index].value"
                                            type="text"
                                            class="w-full"
                                            :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                                        />

                                        <div v-else-if="setting.type === 'boolean'" class="flex items-center space-x-3">
                                            <input
                                                :id="setting.key"
                                                v-model="form.settings[index].value"
                                                type="checkbox"
                                                class="w-4 h-4 rounded border-gray-300 focus:ring-2 focus:ring-blue-500"
                                            />
                                            <label :for="setting.key" class="text-sm text-gray-700">เปิดใช้งาน</label>
                                        </div>

                                        <div v-if="form.errors[`settings.${index}.value`]" class="text-sm text-red-500">
                                            {{ form.errors[`settings.${index}.value`] }}
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4 border-t">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            >
                                <Save class="mr-2 h-4 w-4" />
                                <span v-if="form.processing">กำลังบันทึก...</span>
                                <span v-else>บันทึกการตั้งค่า</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>