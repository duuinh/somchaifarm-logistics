<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { PlusCircle, Pencil, Trash2, Eye, Search } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import Pagination from '@/components/Pagination.vue';

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

interface PaginatedData {
    current_page: number;
    data: User[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: any[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    users: PaginatedData;
    filters?: {
        search?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search || '');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'จัดการผู้ใช้',
        href: '/users',
    },
];

// Server-side search with debounce
const performSearch = useDebounceFn(() => {
    router.get('/users', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(searchQuery, () => {
    performSearch();
});

const formatDate = (dateString: string) => {
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

const deleteUser = (id: number) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?')) {
        router.delete(`/users/${id}`);
    }
};
</script>

<template>
    <Head title="จัดการผู้ใช้" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>จัดการผู้ใช้</CardTitle>
                            <CardDescription>จัดการบัญชีผู้ใช้ในระบบ</CardDescription>
                        </div>
                        <Link :href="route('users.create')">
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                เพิ่มผู้ใช้ใหม่
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex items-center gap-2">
                        <div class="relative max-w-sm">
                            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="ค้นหาชื่อ หรืออีเมล..."
                                class="pl-8"
                            />
                        </div>
                        <div class="text-sm text-muted-foreground">
                            พบ {{ users.total }} รายการ
                        </div>
                    </div>
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>ชื่อผู้ใช้</TableHead>
                                        <TableHead>อีเมล</TableHead>
                                        <TableHead>บทบาท</TableHead>
                                        <TableHead>สถานะ</TableHead>
                                        <TableHead>วันที่สร้าง</TableHead>
                                        <TableHead class="text-right">การดำเนินการ</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="user in users.data" :key="user.id">
                                        <TableCell class="font-medium">
                                            {{ user.name }}
                                        </TableCell>
                                        <TableCell>
                                            {{ user.email }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge :class="getRoleBadge(user.role).class">
                                                {{ getRoleBadge(user.role).label }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusBadge(user.status, user.email_verified_at).class">
                                                {{ getStatusBadge(user.status, user.email_verified_at).label }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {{ formatDate(user.created_at) }}
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <Link :href="route('users.show', user.id)">
                                                    <Button variant="ghost" size="sm">
                                                        <Eye class="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Link :href="route('users.edit', user.id)">
                                                    <Button variant="ghost" size="sm">
                                                        <Pencil class="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="deleteUser(user.id)"
                                                >
                                                    <Trash2 class="h-4 w-4 text-red-500" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div v-if="users.data.length === 0" class="text-center py-8">
                            <p class="text-muted-foreground">ไม่พบผู้ใช้ที่ตรงกับเงื่อนไขการค้นหา</p>
                        </div>

                        <!-- Pagination -->
                        <Pagination :data="users" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>