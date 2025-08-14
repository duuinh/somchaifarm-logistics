<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    FileText, 
    Truck, 
    MapPin, 
    Users, 
    Package, 
    Clock, 
    CheckCircle, 
    AlertCircle,
    TrendingUp,
    Calendar,
    Activity
} from 'lucide-vue-next';

interface DeliveryNote {
    id: number;
    delivery_date: string;
    total_amount: number;
    pricing_type: 'regular' | 'credit';
    client: {
        name: string;
        address?: string;
    } | null;
    driver: {
        name: string;
    } | null;
    vehicle: {
        license_plate: string;
    } | null;
    // Future tracking fields
    status?: 'pending' | 'in_transit' | 'delivered' | 'cancelled';
    created_at: string;
}

interface Props {
    stats: {
        delivery_notes_count: number;
        clients_count: number;
        items_count: number;
        drivers_count: number;
        vehicles_count: number;
    };
    recent_delivery_notes: DeliveryNote[];
}

defineProps<Props>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year}`;
};

const formatDateTime = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
};

const getDeliveryStatus = (deliveryNote: DeliveryNote) => {
    // For now, simulate status based on delivery date
    // In the future, this will come from vehicle tracking API
    const deliveryDate = new Date(deliveryNote.delivery_date);
    const today = new Date();
    const isToday = deliveryDate.toDateString() === today.toDateString();
    const isPast = deliveryDate < today;
    
    if (isPast && !isToday) {
        return { status: 'delivered', label: 'ส่งแล้ว', color: 'bg-green-100 text-green-800' };
    } else if (isToday) {
        return { status: 'in_transit', label: 'กำลังส่ง', color: 'bg-blue-100 text-blue-800' };
    } else {
        return { status: 'pending', label: 'รอส่ง', color: 'bg-yellow-100 text-yellow-800' };
    }
};

const getPricingTypeBadge = (type: string) => {
    const badges = {
        regular: { label: 'ปกติ', class: 'bg-blue-100 text-blue-800' },
        credit: { label: 'เครดิต', class: 'bg-green-100 text-green-800' },
    };
    return badges[type as keyof typeof badges] || badges.regular;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="แดชบอร์ด - ระบบโลจิสติกส์" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">ระบบโลจิสติกส์สมชายฟาร์ม</h1>
                    <p class="text-muted-foreground">จัดการและติดตามการขนส่งแบบเรียลไทม์</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('delivery-notes.create')">
                        <Button>
                            <FileText class="mr-2 h-4 w-4" />
                            สร้างใบส่งของ
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-sm font-medium">ยอดรวมวันนี้</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">₿125,400</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            <span class="text-green-600">สด ₿45,200</span> • <span class="text-blue-600">โอน ₿80,200</span>
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-sm font-medium">ใบส่งของทั้งหมด</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ $props.stats.delivery_notes_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            จำนวนใบส่งของในระบบ
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-sm font-medium">ลูกค้า</CardTitle>
                        <MapPin class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ $props.stats.clients_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            จำนวนลูกค้าทั้งหมด
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-sm font-medium">สินค้า</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ $props.stats.items_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            รายการสินค้าในระบบ
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-4 lg:grid-cols-2">
                <!-- Delivery Status Overview -->
                <div class="lg:col-span-1">
                    <Card class="h-full">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <Activity class="h-5 w-5" />
                                        สถานะการส่ง
                                    </CardTitle>
                                    <CardDescription>ติดตามการส่งของแบบเรียลไทม์</CardDescription>
                                </div>
                                <Link :href="route('delivery-notes.index')">
                                    <Button variant="outline" size="sm">ดูทั้งหมด</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="$props.recent_delivery_notes && $props.recent_delivery_notes.length > 0" class="space-y-4 max-h-96 overflow-y-auto">
                                <div v-for="deliveryNote in $props.recent_delivery_notes.slice(0, 4)" :key="deliveryNote.id" class="border rounded-lg p-4 hover:shadow-sm transition-shadow">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <Badge :class="getDeliveryStatus(deliveryNote).color" class="text-xs">
                                                {{ getDeliveryStatus(deliveryNote).label }}
                                            </Badge>
                                            <Badge :class="getPricingTypeBadge(deliveryNote.pricing_type).class" variant="outline" class="text-xs">
                                                {{ getPricingTypeBadge(deliveryNote.pricing_type).label }}
                                            </Badge>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold">₿{{ deliveryNote.total_amount?.toLocaleString('th-TH', { minimumFractionDigits: 2 }) || '0.00' }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">ใบส่งของ #{{ deliveryNote.id }}</span>
                                            <span class="text-xs text-muted-foreground">{{ formatDate(deliveryNote.delivery_date) }}</span>
                                        </div>
                                        
                                        <div class="space-y-1 text-sm text-muted-foreground">
                                            <div class="flex items-center gap-2">
                                                <MapPin class="h-3 w-3" />
                                                <span>{{ deliveryNote.client?.name || 'ไม่ระบุลูกค้า' }}</span>
                                            </div>
                                            <div v-if="deliveryNote.vehicle" class="flex items-center gap-2">
                                                <Truck class="h-3 w-3" />
                                                <span>{{ deliveryNote.vehicle.license_plate }} • {{ deliveryNote.driver?.name || 'ไม่ระบุคนขับ' }}</span>
                                            </div>
                                        </div>

                                        <div class="flex justify-end mt-2">
                                            <Link :href="route('delivery-notes.show', deliveryNote.id)">
                                                <Button variant="ghost" size="sm">
                                                    ดูรายละเอียด
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Activity class="mx-auto h-12 w-12 mb-4 opacity-50" />
                                <p class="mb-2">ไม่มีการส่งของในขณะนี้</p>
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
                </div>

                <!-- Fleet & Operations Overview -->
                <div class="space-y-4">
                    <!-- Fleet Status -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Truck class="h-5 w-5" />
                                สถานะยานพาหนะ
                            </CardTitle>
                            <CardDescription>จำนวนรถทั้งหมด: {{ $props.stats.vehicles_count }} คัน</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center p-3 bg-green-50 rounded-lg">
                                    <div class="text-xl font-bold text-green-700">{{ Math.max(0, $props.stats.vehicles_count - 6) }}</div>
                                    <div class="text-xs text-green-600 flex items-center justify-center gap-1 mt-1">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        พร้อมใช้งาน
                                    </div>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <div class="text-xl font-bold text-blue-700">{{ Math.min(4, $props.stats.vehicles_count) }}</div>
                                    <div class="text-xs text-blue-600 flex items-center justify-center gap-1 mt-1">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        กำลังส่ง
                                    </div>
                                </div>
                                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                    <div class="text-xl font-bold text-yellow-700">{{ Math.min(2, $props.stats.vehicles_count) }}</div>
                                    <div class="text-xs text-yellow-600 flex items-center justify-center gap-1 mt-1">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                        ซ่อมบำรุง
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-t">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">คนขับทั้งหมด</span>
                                    <span class="font-medium">{{ $props.stats.drivers_count }} คน</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Today's Progress -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Clock class="h-5 w-5" />
                                ความคืบหน้าวันนี้
                            </CardTitle>
                            <CardDescription>สรุปการส่งของประจำวัน</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <CheckCircle class="h-5 w-5 text-green-500" />
                                        <div>
                                            <div class="font-medium text-green-900">ส่งแล้ว</div>
                                            <div class="text-xs text-green-600">เสร็จสิ้นแล้ว</div>
                                        </div>
                                    </div>
                                    <div class="text-xl font-bold text-green-700">8</div>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <Activity class="h-5 w-5 text-blue-500" />
                                        <div>
                                            <div class="font-medium text-blue-900">กำลังส่ง</div>
                                            <div class="text-xs text-blue-600">อยู่ระหว่างดำเนินการ</div>
                                        </div>
                                    </div>
                                    <div class="text-xl font-bold text-blue-700">4</div>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <AlertCircle class="h-5 w-5 text-yellow-500" />
                                        <div>
                                            <div class="font-medium text-yellow-900">รอส่ง</div>
                                            <div class="text-xs text-yellow-600">รอดำเนินการ</div>
                                        </div>
                                    </div>
                                    <div class="text-xl font-bold text-yellow-700">2</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
