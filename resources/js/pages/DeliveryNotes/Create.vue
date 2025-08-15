<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Save, FileText } from 'lucide-vue-next';
import { computed } from 'vue';
import { generateNotesTemplate } from '@/utils/notesTemplate';

// Import types
import type { Client, Item, Driver, Vehicle } from '@/types/delivery-notes';

// Import composable
import { useDeliveryNote } from '@/composables/useDeliveryNote';

// Import components
import ItemsTable from '@/components/DeliveryNotes/ItemsTable.vue';
import AddItemForm from '@/components/DeliveryNotes/AddItemForm.vue';
import ServiceFeesTable from '@/components/DeliveryNotes/ServiceFeesTable.vue';
import SummarySection from '@/components/DeliveryNotes/SummarySection.vue';

interface Props {
    clients: Client[];
    items: Item[];
    drivers: Driver[];
    vehicles: Vehicle[];
    defaultServiceFee: number;
}

const props = defineProps<Props>();

// Use the composable
const {
    form,
    selectedClient,
    newItemId,
    newUnitType,
    newQuantity,
    newUnitPrice,
    includeServiceFee,
    includeBagFee,
    includeTransportFee,
    calculatedServiceFee,
    totalWeight,
    totalAmount,
    getItemName,
    getSelectedItemInfo,
    formatBahtText,
    addItem,
    removeItem,
    resetAddItemForm,
} = useDeliveryNote(props);

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
        title: 'สร้างใบส่งของใหม่',
        href: '/delivery-notes/create',
    },
];

// Template insertion function
const insertNotesTemplate = () => {
    form.notes = generateNotesTemplate({
        drivers: props.drivers,
        vehicles: props.vehicles,
        driverId: form.driver_id,
        vehicleId: form.vehicle_id,
        existingNotes: form.notes
    });
};

// Form submission
const submit = () => {
    // Set fees to 0 if their checkbox is not checked
    if (!includeServiceFee.value) {
        form.service_fee = 0;
        form.service_fee_tons = 0;
    }
    if (!includeBagFee.value) {
        form.bag_fee = 0;
    }
    if (!includeTransportFee.value) {
        form.transport_fee = 0;
    }
    
    form.post(route('delivery-notes.store'));
};

// Computed values for summary
const itemsTotal = computed(() => {
    return form.items.reduce((total, item) => total + (item.total_price || 0), 0);
});

const serviceFee = computed(() => {
    return includeServiceFee.value ? calculatedServiceFee.value : 0;
});

const bagFee = computed(() => {
    return includeBagFee.value ? (form.bag_fee || 0) : 0;
});

const transportFee = computed(() => {
    return includeTransportFee.value ? (form.transport_fee || 0) : 0;
});
</script>

<template>
    <Head title="สร้างใบส่งของใหม่" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Header -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-4">
                            <Link :href="route('delivery-notes.index')">
                                <Button variant="ghost" size="sm">
                                    <ArrowLeft class="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <CardTitle>สร้างใบส่งของใหม่</CardTitle>
                                <CardDescription>กรอกข้อมูลใบส่งของ</CardDescription>
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
                                        {{ String(client.id).padStart(3, '0') }} - {{ client.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.client_id" class="text-sm text-red-500">
                                    {{ form.errors.client_id }}
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
                                        <span v-if="vehicle.load_capacity">({{ vehicle.load_capacity }} ตัน)</span>
                                    </option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2 md:col-span-2">
                                <div class="flex items-center justify-between">
                                    <Label for="notes">หมายเหตุ</Label>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="insertNotesTemplate"
                                        class="text-xs"
                                    >
                                        <FileText class="h-3 w-3 mr-1" />
                                        ใช้เทมเพลท
                                    </Button>
                                </div>
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
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>รายการสินค้า</CardTitle>
                                <CardDescription>เพิ่มสินค้าที่จะส่ง</CardDescription>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">ประเภทราคา *</Label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input
                                            v-model="form.pricing_type"
                                            type="radio"
                                            value="regular"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                        />
                                        <span class="text-sm font-medium">ราคาปกติ</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input
                                            v-model="form.pricing_type"
                                            type="radio"
                                            value="credit"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                        />
                                        <span class="text-sm font-medium">ราคาเครดิต</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Items Table -->
                        <ItemsTable
                            :items="form.items"
                            :getItemName="getItemName"
                            :allItems="items"
                            @removeItem="removeItem"
                        />

                        <!-- Add Item Form -->
                        <AddItemForm
                            :items="items"
                            :item-id="newItemId"
                            :unit-type="newUnitType"
                            :quantity="newQuantity"
                            :unit-price="newUnitPrice"
                            :selected-item-info="getSelectedItemInfo()"
                            @update:item-id="newItemId = $event"
                            @update:unit-type="newUnitType = $event"
                            @update:quantity="newQuantity = $event"
                            @update:unit-price="newUnitPrice = $event"
                            @add-item="addItem"
                        />

                        <!-- Service Fees Table -->
                        <ServiceFeesTable
                            :include-service-fee="includeServiceFee"
                            :include-bag-fee="includeBagFee"
                            :include-transport-fee="includeTransportFee"
                            :service-feetons="form.service_fee_tons"
                            :service-fee-per-ton="form.service_fee_per_ton"
                            :bag-fee="form.bag_fee"
                            :transport-fee="form.transport_fee"
                            :calculated-service-fee="calculatedServiceFee"
                            @update:include-service-fee="includeServiceFee = $event"
                            @update:include-bag-fee="includeBagFee = $event"
                            @update:include-transport-fee="includeTransportFee = $event"
                            @update:service-feetons="form.service_fee_tons = $event"
                            @update:service-fee-per-ton="form.service_fee_per_ton = $event"
                            @update:bag-fee="form.bag_fee = $event"
                            @update:transport-fee="form.transport_fee = $event"
                        />

                        <!-- Summary -->
                        <SummarySection
                            :total-weight="totalWeight"
                            :items-total="itemsTotal"
                            :service-fee="serviceFee"
                            :bag-fee="bagFee"
                            :transport-fee="transportFee"
                            :total-amount="totalAmount"
                            :baht-text="formatBahtText(totalAmount)"
                        />
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
                        บันทึกใบส่งของ
                    </Button>
                    <Link :href="route('delivery-notes.index')">
                        <Button variant="outline" size="lg">
                            ยกเลิก
                        </Button>
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>