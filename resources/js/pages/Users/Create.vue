<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Save, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

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
        title: 'เพิ่มผู้ใช้ใหม่',
        href: '/users/create',
    },
];

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'operator',
    status: 'active',
    send_welcome_email: true,
});

const submit = () => {
    form.post(route('users.store'));
};

const roles = [
    { value: 'admin', label: 'ผู้ดูแลระบบ', description: 'สิทธิ์เต็มในการจัดการระบบ' },
    { value: 'operator', label: 'พนักงานออกบิล', description: 'จัดการใบส่งของและข้อมูลลูกค้า' },
];

const statuses = [
    { value: 'active', label: 'ใช้งานได้', description: 'ผู้ใช้สามารถเข้าสู่ระบบได้' },
    { value: 'inactive', label: 'ระงับการใช้งาน', description: 'ผู้ใช้ไม่สามารถเข้าสู่ระบบได้' },
];
</script>

<template>
    <Head title="เพิ่มผู้ใช้ใหม่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-2xl mx-auto space-y-6">
                <!-- Header -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Link :href="route('users.index')">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <div>
                                    <CardTitle>เพิ่มผู้ใช้ใหม่</CardTitle>
                                    <CardDescription>สร้างบัญชีผู้ใช้ใหม่ในระบบ</CardDescription>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Create Form -->
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>ข้อมูลผู้ใช้</CardTitle>
                            <CardDescription>กรอกข้อมูลสำหรับสร้างบัญชีผู้ใช้ใหม่</CardDescription>
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

                                <!-- Password -->
                                <div class="space-y-2">
                                    <Label for="password">รหัสผ่าน *</Label>
                                    <div class="relative">
                                        <Input
                                            id="password"
                                            :type="showPassword ? 'text' : 'password'"
                                            v-model="form.password"
                                            :class="form.errors.password ? 'border-red-500' : ''"
                                            placeholder="กรอกรหัสผ่าน"
                                            required
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
                                    <Label for="password_confirmation">ยืนยันรหัสผ่าน *</Label>
                                    <div class="relative">
                                        <Input
                                            id="password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            v-model="form.password_confirmation"
                                            :class="form.errors.password_confirmation ? 'border-red-500' : ''"
                                            placeholder="กรอกรหัสผ่านอีกครั้ง"
                                            required
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

                            <!-- Additional Options -->
                            <div class="space-y-4 pt-4 border-t">
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="send_welcome_email" v-model:checked="form.send_welcome_email" />
                                    <Label for="send_welcome_email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        ส่งอีเมลต้อนรับให้กับผู้ใช้ใหม่
                                    </Label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6">
                                    ผู้ใช้จะได้รับอีเมลพร้อมข้อมูลการเข้าใช้งานและลิงก์ยืนยันอีเมล
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit Actions -->
                    <Card>
                        <CardContent class="pt-6">
                            <div class="flex items-center justify-between">
                                <Link :href="route('users.index')">
                                    <Button type="button" variant="outline">
                                        ยกเลิก
                                    </Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ form.processing ? 'กำลังสร้าง...' : 'สร้างผู้ใช้' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>
    </AppLayout>
</template>