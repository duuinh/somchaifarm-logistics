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
        title: 'การตั้งค่าระบบ',
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
        'default_service_fee': 'ค่าผสมเริ่มต้น (บาท)',
        'company_name': 'ชื่อบริษัท',
        'company_address': 'ที่อยู่บริษัท',
        'company_phone': 'เบอร์โทรศัพท์บริษัท',
    };
    return labels[key] || key;
};
</script>

<template>
    <Head title="การตั้งค่าระบบ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="w-full max-w-6xl mx-auto">
                <CardHeader>
                    <CardTitle>การตั้งค่าระบบ</CardTitle>
                    <CardDescription>จัดการการตั้งค่าต่างๆ ของระบบ</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div v-for="(setting, index) in form.settings" :key="setting.key" class="space-y-2">
                            <Label :for="setting.key">{{ getLabel(setting.key) }}</Label>
                            
                            <!-- String/Text fields -->
                            <Input
                                v-if="setting.type === 'string' && setting.key !== 'company_address'"
                                :id="setting.key"
                                v-model="form.settings[index].value"
                                type="text"
                                :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                            />
                            
                            <!-- Textarea for address -->
                            <Textarea
                                v-else-if="setting.key === 'company_address'"
                                :id="setting.key"
                                v-model="form.settings[index].value"
                                rows="3"
                                :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                            />
                            
                            <!-- Number fields -->
                            <Input
                                v-else-if="setting.type === 'number'"
                                :id="setting.key"
                                v-model.number="form.settings[index].value"
                                type="number"
                                step="0.01"
                                :class="{ 'border-red-500': form.errors[`settings.${index}.value`] }"
                            />
                            
                            <!-- Boolean fields -->
                            <div v-else-if="setting.type === 'boolean'" class="flex items-center space-x-2">
                                <input
                                    :id="setting.key"
                                    v-model="form.settings[index].value"
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />
                                <label :for="setting.key" class="text-sm">เปิดใช้งาน</label>
                            </div>
                            
                            <div v-if="form.errors[`settings.${index}.value`]" class="text-sm text-red-500">
                                {{ form.errors[`settings.${index}.value`] }}
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                บันทึกการตั้งค่า
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>