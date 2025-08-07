<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';

interface Client {
    id: number;
    name: string;
}

interface Item {
    id: number;
    name: string;
    regular_price_per_kg?: number;
    regular_price_per_bag?: number;
    credit_price_per_kg?: number;
    credit_price_per_bag?: number;
    kg_per_bag_conversion: number;
}

interface Driver {
    id: number;
    name: string;
}

interface Vehicle {
    id: number;
    license_plate: string;
}

interface DeliveryNoteItem {
    id?: number;
    item_id: number;
    quantity_kg?: number;
    quantity_bags?: number;
    quantity_tons?: number;
    quantity_units?: number;
    unit_multiplier: number;
    unit_price: number;
    total_price: number;
    item?: Item;
}

interface DeliveryNote {
    id: number;
    client_id: number;
    driver_id?: number;
    vehicle_id?: number;
    delivery_date: string;
    pricing_type: 'regular' | 'credit';
    notes?: string;
    service_fee?: number;
    items: DeliveryNoteItem[];
}

interface Props {
    deliveryNote: DeliveryNote;
    clients: Client[];
    items: Item[];
    drivers: Driver[];
    vehicles: Vehicle[];
    defaultServiceFee: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
    },
    {
        title: 'ใบส่งของ',
        href: '/delivery-notes',
    },
    {
        title: `แก้ไขใบส่งของ #${props.deliveryNote.id}`,
        href: `/delivery-notes/${props.deliveryNote.id}/edit`,
    },
];

const form = useForm({
    client_id: props.deliveryNote.client_id.toString(),
    driver_id: props.deliveryNote.driver_id?.toString() || '',
    vehicle_id: props.deliveryNote.vehicle_id?.toString() || '',
    delivery_date: props.deliveryNote.delivery_date,
    pricing_type: props.deliveryNote.pricing_type,
    notes: props.deliveryNote.notes || '',
    service_fee: props.deliveryNote.service_fee || props.defaultServiceFee,
    items: props.deliveryNote.items.map(item => ({
        id: item.id,
        item_id: item.item_id,
        quantity_kg: item.quantity_kg,
        quantity_bags: item.quantity_bags,
        quantity_tons: item.quantity_tons,
        quantity_units: item.quantity_units,
        unit_multiplier: item.unit_multiplier,
        unit_price: item.unit_price,
        total_price: item.total_price,
    })),
});

const selectedClient = ref<Client | null>(null);
const newItemId = ref('');
const newQuantityKg = ref<number | null>(null);
const newQuantityBags = ref<number | null>(null);

// Initialize selected client
onMounted(() => {
    const client = props.clients.find(c => c.id.toString() === form.client_id);
    if (client) {
        selectedClient.value = client;
    }
});

// Watch for client selection to set default pricing type
watch(() => form.client_id, (clientId) => {
    const client = props.clients.find(c => c.id.toString() === clientId);
    if (client) {
        selectedClient.value = client;
        // Default to regular pricing, but allow manual override
        form.pricing_type = 'regular';
    }
});

const calculateItemPrice = (item: Item, quantityKg: number | null, quantityBags: number | null) => {
    const pricingType = form.pricing_type;
    let totalPrice = 0;

    if (quantityKg && quantityKg > 0) {
        const pricePerKg = pricingType === 'credit' ? item.credit_price_per_kg : item.regular_price_per_kg;
        totalPrice += quantityKg * (pricePerKg || 0);
    }

    if (quantityBags && quantityBags > 0) {
        const pricePerBag = pricingType === 'credit' ? item.credit_price_per_bag : item.regular_price_per_bag;
        totalPrice += quantityBags * (pricePerBag || 0);
    }

    return totalPrice;
};

const addItem = () => {
    if (!newItemId.value || (!newQuantityKg.value && !newQuantityBags.value)) return;

    const item = props.items.find(i => i.id.toString() === newItemId.value.toString());
    if (!item) return;

    const totalPrice = calculateItemPrice(item, newQuantityKg.value, newQuantityBags.value);
    const unitPrice = totalPrice / ((newQuantityKg.value || 0) + (newQuantityBags.value || 0) * item.kg_per_bag_conversion);

    form.items.push({
        item_id: item.id,
        quantity_kg: newQuantityKg.value,
        quantity_bags: newQuantityBags.value,
        quantity_tons: null,
        quantity_units: null,
        unit_multiplier: 1,
        unit_price: unitPrice,
        total_price: totalPrice,
    });

    // Reset form
    newItemId.value = '';
    newQuantityKg.value = null;
    newQuantityBags.value = null;
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};


const totalAmount = computed(() => {
    const itemsTotal = form.items.reduce((total, item) => total + item.total_price, 0);
    return itemsTotal + (form.service_fee || 0);
});

const totalWeight = computed(() => {
    const weight = form.items.reduce((total, item) => {
        let itemWeight = 0;
        if (item.quantity_kg && !isNaN(Number(item.quantity_kg))) {
            itemWeight += Number(item.quantity_kg);
        }
        if (item.quantity_bags && !isNaN(Number(item.quantity_bags))) {
            const itemData = props.items.find(i => i.id === item.item_id);
            itemWeight += Number(item.quantity_bags) * (itemData?.kg_per_bag_conversion || 25);
        }
        return total + itemWeight;
    }, 0);
    
    return isNaN(weight) || !isFinite(weight) ? 0 : Number(weight);
});

const submit = () => {
    form.put(route('delivery-notes.update', props.deliveryNote.id));
};

const getItemName = (itemId: number) => {
    return props.items.find(i => i.id === itemId)?.name || '';
};
</script>

<template>
    <Head :title="`แก้ไขใบส่งของ #${deliveryNote.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Header -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-4">
                            <Link :href="route('delivery-notes.show', deliveryNote.id)">
                                <Button variant="ghost" size="sm">
                                    <ArrowLeft class="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <CardTitle>แก้ไขใบส่งของ #{{ deliveryNote.id }}</CardTitle>
                                <CardDescription>แก้ไขข้อมูลใบส่งของ</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Client Selection -->
                            <div class="space-y-2">
                                <Label for="client_id">ลูกค้า *</Label>
                                <select
                                    id="client_id"
                                    v-model="form.client_id"
                                    class="flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">เลือกลูกค้า</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">
                                        {{ client.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.client_id" class="text-sm text-red-500">
                                    {{ form.errors.client_id }}
                                </div>
                            </div>

                            <!-- Pricing Type -->
                            <div class="space-y-2">
                                <Label>ประเภทราคา *</Label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input
                                            v-model="form.pricing_type"
                                            type="radio"
                                            value="regular"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                        />
                                        <span class="text-sm">ราคาปกติ</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input
                                            v-model="form.pricing_type"
                                            type="radio"
                                            value="credit"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                        />
                                        <span class="text-sm">ราคาเครดิต</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Delivery Date -->
                            <div class="space-y-2">
                                <Label for="delivery_date">วันที่ส่ง *</Label>
                                <Input
                                    id="delivery_date"
                                    v-model="form.delivery_date"
                                    type="date"
                                    required
                                />
                            </div>

                            <!-- Driver -->
                            <div class="space-y-2">
                                <Label for="driver_id">คนขับ</Label>
                                <select
                                    id="driver_id"
                                    v-model="form.driver_id"
                                    class="flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">เลือกคนขับ</option>
                                    <option v-for="driver in drivers" :key="driver.id" :value="driver.id">
                                        {{ driver.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Vehicle -->
                            <div class="space-y-2">
                                <Label for="vehicle_id">รถ</Label>
                                <select
                                    id="vehicle_id"
                                    v-model="form.vehicle_id"
                                    class="flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">เลือกรถ</option>
                                    <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                                        {{ vehicle.license_plate }}
                                    </option>
                                </select>
                            </div>

                            <!-- Service Fee -->
                            <div class="space-y-2">
                                <Label for="service_fee">ค่าผสม (บาท)</Label>
                                <Input
                                    id="service_fee"
                                    v-model="form.service_fee"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="notes">หมายเหตุ</Label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                ></textarea>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Items Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>รายการสินค้า</CardTitle>
                        <CardDescription>แก้ไขรายการสินค้าที่จะส่ง</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <!-- Add Item Form -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 border rounded-lg bg-gray-50">
                            <div class="space-y-2">
                                <Label>สินค้า</Label>
                                <select
                                    v-model="newItemId"
                                    class="flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-white px-3 py-2 text-sm shadow-sm"
                                >
                                    <option value="">เลือกสินค้า</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label>จำนวน (กก.)</Label>
                                <Input
                                    v-model="newQuantityKg"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>จำนวน (ถุง)</Label>
                                <Input
                                    v-model="newQuantityBags"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>&nbsp;</Label>
                                <Button type="button" @click="addItem" class="w-full">
                                    <Plus class="mr-2 h-4 w-4" />
                                    เพิ่ม
                                </Button>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div v-if="form.items.length > 0" class="space-y-4">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>รายการ</TableHead>
                                        <TableHead>จำนวน (กก.)</TableHead>
                                        <TableHead>จำนวน (ถุง)</TableHead>
                                        <TableHead>ราคารวม</TableHead>
                                        <TableHead class="w-20"></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(item, index) in form.items" :key="index">
                                        <TableCell>{{ getItemName(item.item_id) }}</TableCell>
                                        <TableCell>{{ item.quantity_kg || '-' }}</TableCell>
                                        <TableCell>{{ item.quantity_bags || '-' }}</TableCell>
                                        <TableCell>{{ item.total_price.toLocaleString() }} บาท</TableCell>
                                        <TableCell>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                @click="removeItem(index)"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>

                            <!-- Summary -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="font-medium">น้ำหนักรวม:</span>
                                        <span>{{ Number(totalWeight).toFixed(2) }} กก.</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>รวมค่าสินค้า:</span>
                                        <span>{{ form.items.reduce((total, item) => total + item.total_price, 0).toLocaleString() }} บาท</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>ค่าผสม:</span>
                                        <span>{{ (form.service_fee || 0).toLocaleString() }} บาท</span>
                                    </div>
                                    <div class="border-t pt-2 flex justify-between">
                                        <span class="font-medium text-lg">รวมทั้งหมด:</span>
                                        <span class="font-medium text-lg">{{ totalAmount.toLocaleString() }} บาท</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-8 text-muted-foreground">
                            ยังไม่มีรายการสินค้า กรุณาเพิ่มสินค้า
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex gap-4 justify-center">
                    <Button 
                        type="submit" 
                        :disabled="form.processing || form.items.length === 0"
                        size="lg"
                    >
                        <Save class="mr-2 h-4 w-4" />
                        บันทึกการแก้ไข
                    </Button>
                    <Link :href="route('delivery-notes.show', deliveryNote.id)">
                        <Button variant="outline" size="lg">
                            ยกเลิก
                        </Button>
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>