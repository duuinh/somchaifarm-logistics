<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Pencil, Mail, Calendar, Shield, Activity } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    status: 'active' | 'inactive';
    email_verified_at?: string;
    last_login_at?: string;
    created_at: string;
    updated_at: string;
    created_by?: {
        id: number;
        name: string;
    };
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
        title: `ผู้ใช้ #${props.user.id}`,
        href: `/users/${props.user.id}`,
    },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
};

const formatDateOnly = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

const getRoleBadge = (role: string) => {
    const badges = {
        admin: { label: 'ผู้ดูแลระบบ', class: 'bg-red-100 text-red-800 hover:bg-red-100' },
        operator: { label: 'พนักงานออกบิล', class: 'bg-blue-100 text-blue-800 hover:bg-blue-100' },
    };
    return badges[role as keyof typeof badges] || { label: role, class: 'bg-gray-100 text-gray-800 hover:bg-gray-100' };
};

const getStatusBadge = (status: string, emailVerified: string | undefined) => {
    if (!emailVerified) {
        return { label: 'รอยืนยันอีเมล', class: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100' };
    }
    
    const badges = {
        active: { label: 'ใช้งานได้', class: 'bg-green-100 text-green-800 hover:bg-green-100' },
        inactive: { label: 'ระงับการใช้งาน', class: 'bg-red-100 text-red-800 hover:bg-red-100' },
    };
    return badges[status as keyof typeof badges] || badges.active;
};
</script>

<template>
    <Head :title="`ผู้ใช้ #${user.id} - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full max-w-4xl mx-auto space-y-6">
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
                                    <CardTitle>{{ user.name }}</CardTitle>
                                    <CardDescription>รายละเอียดผู้ใช้ #{{ user.id }}</CardDescription>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('users.edit', user.id)">
                                    <Button>
                                        <Pencil class="mr-2 h-4 w-4" />
                                        แก้ไข
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- User Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Shield class="mr-2 h-5 w-5" />
                                ข้อมูลพื้นฐาน
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">ชื่อผู้ใช้</h3>
                                <p class="text-lg font-semibold">{{ user.name }}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">อีเมล</h3>
                                <div class="flex items-center space-x-2">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <p class="text-sm">{{ user.email }}</p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">บทบาท</h3>
                                <Badge :class="getRoleBadge(user.role).class">
                                    {{ getRoleBadge(user.role).label }}
                                </Badge>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">สถานะ</h3>
                                <Badge :class="getStatusBadge(user.status, user.email_verified_at).class">
                                    {{ getStatusBadge(user.status, user.email_verified_at).label }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Activity Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Activity class="mr-2 h-5 w-5" />
                                กิจกรรมและสถิติ
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">สถานะอีเมล</h3>
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 rounded-full" :class="user.email_verified_at ? 'bg-green-500' : 'bg-yellow-500'"></div>
                                    <p class="text-sm">
                                        {{ user.email_verified_at ? 'ยืนยันแล้ว' : 'รอการยืนยัน' }}
                                    </p>
                                </div>
                                <p v-if="user.email_verified_at" class="text-xs text-muted-foreground mt-1">
                                    ยืนยันเมื่อ {{ formatDate(user.email_verified_at) }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">เข้าใช้งานล่าสุด</h3>
                                <div class="flex items-center space-x-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <p class="text-sm">
                                        {{ user.last_login_at ? formatDate(user.last_login_at) : 'ยังไม่เคยเข้าใช้งาน' }}
                                    </p>
                                </div>
                            </div>

                            <Separator />

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">วันที่สร้างบัญชี</h3>
                                <p class="text-sm">{{ formatDateOnly(user.created_at) }}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">อัปเดตล่าสุด</h3>
                                <p class="text-sm">{{ formatDateOnly(user.updated_at) }}</p>
                            </div>

                            <div v-if="user.created_by">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">สร้างโดย</h3>
                                <p class="text-sm">{{ user.created_by.name }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>