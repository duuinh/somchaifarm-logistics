<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { FileText, Users, Package, Truck, UserCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="แดชบอร์ด - ระบบใบส่งของ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Welcome Section -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold mb-2">ระบบจัดการใบส่งของ</h1>
                <p class="text-muted-foreground">ระบบสร้างและจัดการใบส่งของสำหรับธุรกิจอาหารสัตว์</p>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Create Delivery Note -->
                <Card class="hover:shadow-md transition-shadow cursor-pointer">
                    <Link :href="route('delivery-notes.create')" class="block">
                        <CardHeader class="text-center pb-2">
                            <div class="mx-auto mb-2 h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                <FileText class="h-6 w-6 text-blue-600" />
                            </div>
                            <CardTitle class="text-lg">สร้างใบส่งของใหม่</CardTitle>
                        </CardHeader>
                        <CardContent class="text-center pt-0">
                            <CardDescription>สร้างใบส่งของสำหรับลูกค้า</CardDescription>
                        </CardContent>
                    </Link>
                </Card>

                <!-- Manage Clients -->
                <Card class="hover:shadow-md transition-shadow cursor-pointer">
                    <Link :href="route('clients.index')" class="block">
                        <CardHeader class="text-center pb-2">
                            <div class="mx-auto mb-2 h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                                <Users class="h-6 w-6 text-green-600" />
                            </div>
                            <CardTitle class="text-lg">จัดการลูกค้า</CardTitle>
                        </CardHeader>
                        <CardContent class="text-center pt-0">
                            <CardDescription>เพิ่ม แก้ไข ข้อมูลลูกค้า</CardDescription>
                        </CardContent>
                    </Link>
                </Card>

                <!-- Manage Items -->
                <Card class="hover:shadow-md transition-shadow cursor-pointer">
                    <Link :href="route('items.index')" class="block">
                        <CardHeader class="text-center pb-2">
                            <div class="mx-auto mb-2 h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center">
                                <Package class="h-6 w-6 text-orange-600" />
                            </div>
                            <CardTitle class="text-lg">จัดการสินค้า</CardTitle>
                        </CardHeader>
                        <CardContent class="text-center pt-0">
                            <CardDescription>จัดการรายการสินค้าและราคา</CardDescription>
                        </CardContent>
                    </Link>
                </Card>

                <!-- Manage Drivers & Vehicles -->
                <Card class="hover:shadow-md transition-shadow cursor-pointer">
                    <Link :href="route('drivers.index')" class="block">
                        <CardHeader class="text-center pb-2">
                            <div class="mx-auto mb-2 h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                                <Truck class="h-6 w-6 text-purple-600" />
                            </div>
                            <CardTitle class="text-lg">จัดการคนขับ & รถ</CardTitle>
                        </CardHeader>
                        <CardContent class="text-center pt-0">
                            <CardDescription>จัดการข้อมูลคนขับและรถ</CardDescription>
                        </CardContent>
                    </Link>
                </Card>
            </div>

            <!-- Recent Delivery Notes -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>ใบส่งของล่าสุด</CardTitle>
                            <CardDescription>ใบส่งของที่สร้างล่าสุด</CardDescription>
                        </div>
                        <Link :href="route('delivery-notes.index')">
                            <Button variant="outline">ดูทั้งหมด</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-center py-8 text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 mb-4 opacity-50" />
                        <p>ยังไม่มีใบส่งของ</p>
                        <p class="text-sm mb-4">เริ่มต้นด้วยการสร้างใบส่งของใหม่</p>
                        <Link :href="route('delivery-notes.create')">
                            <Button>
                                <FileText class="mr-2 h-4 w-4" />
                                สร้างใบส่งของแรก
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Stats (placeholder for future) -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">ใบส่งของทั้งหมด</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">0</div>
                        <p class="text-xs text-muted-foreground">รายการ</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">ลูกค้าทั้งหมด</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">0</div>
                        <p class="text-xs text-muted-foreground">รายการ</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">สินค้าทั้งหมด</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">0</div>
                        <p class="text-xs text-muted-foreground">รายการ</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
