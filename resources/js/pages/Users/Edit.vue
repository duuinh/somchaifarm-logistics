<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Save, Eye, EyeOff, Key } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    status: 'active' | 'inactive';
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    user: User;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'จัดการผู้ใช้',
        href: '/users',
    },
    {
        title: `แก้ไขผู้ใช้ #${props.user.id}`,
        href: `/users/${props.user.id}/edit`,
    },
];

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const updatePassword = ref(false);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    status: props.user.status,
    update_password: false,
});

const submit = () => {
    form.update_password = updatePassword.value;
    form.put(route('users.update', props.user.id));
};

const roles = [
    { value: 'admin', label: 'ผู้ดูแลระบบ', description: 'สิทธิ์เต็มในการจัดการระบบ' },
    { value: 'operator', label: 'พนักงานออกบิล', description: 'จัดการใบส่งของและข้อมูลลูกค้า' },
];

const statuses = [
    { value: 'active', label: 'ใช้งานได้', description: 'ผู้ใช้สามารถเข้าสู่ระบบได้' },
    { value: 'inactive', label: 'ระงับการใช้งาน', description: 'ผู้ใช้ไม่สามารถเข้าสู่ระบบได้' },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};
</script>

<template>
    <Head :title="`แก้ไขผู้ใช้ #${user.id} - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-2xl mx-auto space-y-6">
                <!-- Header -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('users.show', user.id)">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div>
                                    <CardTitle>แก้ไข {{ user.name }}</CardTitle>
                                    <CardDescription>แก้ไขข้อมูลผู้ใช้ #{{ user.id }}</CardDescription>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Edit Form -->
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>ข้อมูลผู้ใช้</CardTitle>
                            <CardDescription>แก้ไขข้อมูลพื้นฐานของผู้ใช้</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div class="space-y-2">
                                    <Label for="name">ชื่อผู้ใช้ *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        :class="form.errors.name ? 'border-red-500' : ''"
                                        placeholder="กรอกชื่อผู้ใช้"
                                        required
                                    />
                                    <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <Label for="email">อีเมล *</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        :class="form.errors.email ? 'border-red-500' : ''"
                                        placeholder="กรอกอีเมล"
                                        required
                                    />
                                    <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <!-- Role -->
                                <div class="space-y-2">
                                    <Label for="role">บทบาท *</Label>
                                    <select
                                        id="role"
                                        v-model="form.role"
                                        :class="['flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50', form.errors.role ? 'border-red-500' : '']"
                                        required
                                    >
                                        <option value="">เลือกบทบาท</option>
                                        <option v-for="role in roles" :key="role.value" :value="role.value">
                                            {{ role.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.role" class="text-sm text-red-600">{{ form.errors.role }}</p>
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label for="status">สถานะ *</Label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        :class="['flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50', form.errors.status ? 'border-red-500' : '']"
                                        required
                                    >
                                        <option value="">เลือกสถานะ</option>
                                        <option v-for="status in statuses" :key="status.value" :value="status.value">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.status" class="text-sm text-red-600">{{ form.errors.status }}</p>
                                </div>
                            </div>

                            <!-- Account Info -->
                            <div class="pt-4 border-t">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                    <div>
                                        <h4 class="font-medium text-muted-foreground">สถานะอีเมล</h4>
                                        <p class="mt-1">{{ user.email_verified_at ? 'ยืนยันแล้ว' : 'รอการยืนยัน' }}</p>
                                        <p v-if="user.email_verified_at" class="text-xs text-muted-foreground">
                                            ยืนยันเมื่อ {{ formatDate(user.email_verified_at) }}
                                        </p>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-muted-foreground">สร้างเมื่อ</h4>
                                        <p class="mt-1">{{ formatDate(user.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Password Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Key class="mr-2 h-5 w-5" />
                                การเปลี่ยนรหัสผ่าน
                            </CardTitle>
                            <CardDescription>เปลี่ยนรหัสผ่านของผู้ใช้ (ไม่บังคับ)</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="update_password" v-model:checked="updatePassword" />
                                <Label for="update_password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    เปลี่ยนรหัสผ่าน
                                </Label>
                            </div>

                            <div v-if="updatePassword" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Password -->
                                <div class="space-y-2">
                                    <Label for="password">รหัสผ่านใหม่ *</Label>
                                    <div class="relative">
                                        <Input
                                            id="password"
                                            :type="showPassword ? 'text' : 'password'"
                                            v-model="form.password"
                                            :class="form.errors.password ? 'border-red-500' : ''"
                                            placeholder="กรอกรหัสผ่านใหม่"
                                            :required="updatePassword"
                                        />
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 h-auto p-1"
                                            @click="showPassword = !showPassword"
                                        >
                                            <Eye v-if="!showPassword" class="h-4 w-4" />
                                            <EyeOff v-else class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
                                    <p class="text-xs text-muted-foreground">รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร</p>
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <Label for="password_confirmation">ยืนยันรหัสผ่านใหม่ *</Label>
                                    <div class="relative">
                                        <Input
                                            id="password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            v-model="form.password_confirmation"
                                            :class="form.errors.password_confirmation ? 'border-red-500' : ''"
                                            placeholder="กรอกรหัสผ่านใหม่อีกครั้ง"
                                            :required="updatePassword"
                                        />
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 h-auto p-1"
                                            @click="showConfirmPassword = !showConfirmPassword"
                                        >
                                            <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                                            <EyeOff v-else class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <p v-if="form.errors.password_confirmation" class="text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                                </div>
                            </div>

                            <div v-if="!updatePassword" class="text-sm text-muted-foreground bg-muted p-3 rounded-md">
                                เลือก "เปลี่ยนรหัสผ่าน" หากต้องการเปลี่ยนรหัสผ่านของผู้ใช้
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit Actions -->
                    <Card>
                        <CardContent class="pt-6">
                            <div class="flex items-center justify-between">
                                <Link :href="route('users.show', user.id)">
                                    <Button type="button" variant="outline">
                                        ยกเลิก
                                    </Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ form.processing ? 'กำลังบันทึก...' : 'บันทึกการเปลี่ยนแปลง' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>
    </AppLayout>
</template>