<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Save, FileText } from 'lucide-vue-next';
import { onMounted, computed } from 'vue';
import { generateNotesTemplate } from '@/utils/notesTemplate';

// Import types
import type { Client, Item, Driver, Vehicle, DeliveryNote } from '@/types/delivery-notes';

// Import composable
import { useDeliveryNote } from '@/composables/useDeliveryNote';

// Import components
import ItemsTable from '@/components/DeliveryNotes/ItemsTable.vue';
import AddItemForm from '@/components/DeliveryNotes/AddItemForm.vue';
import ServiceFeesTable from '@/components/DeliveryNotes/ServiceFeesTable.vue';
import SummarySection from '@/components/DeliveryNotes/SummarySection.vue';
import { Combobox } from '@/components/ui/combobox';

interface Props {
    deliveryNote: DeliveryNote;
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
    initializeServiceFeeCheckboxes,
} = useDeliveryNote(props);

const clientOptions = computed(() => {
    return props.clients.map(client => ({
        value: client.id.toString(),
        label: `${String(client.id).padStart(3, '0')} - ${client.name}`
    }));
});

const driverOptions = computed(() => {
    return props.drivers.map(driver => ({
        value: driver.id.toString(),
        label: driver.name
    }));
});

const vehicleOptions = computed(() => {
    return props.vehicles.map(vehicle => ({
        value: vehicle.id.toString(),
        label: vehicle.load_capacity 
            ? `${vehicle.license_plate} (${vehicle.load_capacity} ตัน)`
            : vehicle.license_plate
    }));
});

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

// Initialize checkbox states on mount
onMounted(() => {
    initializeServiceFeeCheckboxes();
});

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
    // Basic form validation
    if (!form.client_id) {
        alert('กรุณาเลือกลูกค้า');
        return;
    }
    
    if (!form.delivery_date) {
        alert('กรุณาระบุวันที่ส่ง');
        return;
    }
    
    if (form.items.length === 0) {
        alert('กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการ');
        return;
    }
    
    // Validate all items have valid quantities and prices
    for (const item of form.items) {
        if (!item.quantity || item.quantity <= 0) {
            alert('จำนวนสินค้าต้องมากกว่า 0');
            return;
        }
        if (item.price_per_unit === null || item.price_per_unit < 0) {
            alert('ราคาต่อหน่วยต้องไม่น้อยกว่า 0');
            return;
        }
    }
    
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
    
    form.put(route('delivery-notes.update', props.deliveryNote.id));
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
                                <Combobox
                                    :model-value="String(form.client_id || '')"
                                    @update:model-value="form.client_id = $event"
                                    :options="clientOptions"
                                    placeholder="พิมพ์เพื่อค้นหาหรือเลือกลูกค้า..."
                                />
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
                                <Combobox
                                    :model-value="String(form.driver_id || '')"
                                    @update:model-value="form.driver_id = $event"
                                    :options="driverOptions"
                                    placeholder="พิมพ์เพื่อค้นหาหรือเลือกคนขับ..."
                                />
                            </div>

                            <!-- Vehicle -->
                            <div class="space-y-2">
                                <Label for="vehicle_id">รถ</Label>
                                <Combobox
                                    :model-value="String(form.vehicle_id || '')"
                                    @update:model-value="form.vehicle_id = $event"
                                    :options="vehicleOptions"
                                    placeholder="พิมพ์เพื่อค้นหาหรือเลือกรถ..."
                                />
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
                                <CardDescription>แก้ไขรายการสินค้าที่จะส่ง</CardDescription>
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