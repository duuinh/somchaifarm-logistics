<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Printer, RotateCcw, X } from 'lucide-vue-next';
import { bahttext } from "bahttext";

interface Item {
    id: number;
    name: string;
}

interface DeliveryNoteItem {
    id: number;
    quantity_kg?: number;
    quantity_bags?: number;
    unit_multiplier: number;
    unit_price: number;
    total_price: number;
    item: Item;
}

interface Client {
    id: number;
    name: string;
    address?: string;
    phone?: string;
}

interface Driver {
    id: number;
    name: string;
    phone?: string;
}

interface Vehicle {
    id: number;
    license_plate: string;
    province?: string;
}

interface User {
    id: number;
    name: string;
}

interface DeliveryNote {
    id: number;
    delivery_date: string;
    pricing_type: 'regular' | 'credit';
    total_weight?: number;
    total_amount?: number;
    service_fee?: number;
    service_fee_per_ton?: number;
    bag_fee?: number;
    transport_fee?: number;
    notes?: string;
    client: Client;
    driver?: Driver;
    vehicle?: Vehicle;
    creator: User;
    items: DeliveryNoteItem[];
    created_at: string;
}

interface Props {
    deliveryNote: DeliveryNote;
}

const props = defineProps<Props>();

const isFlipped = ref(false);

const toggleFlip = () => {
    isFlipped.value = !isFlipped.value;
};

const handlePrint = () => {
    window.print();
};

const handleClose = () => {
    // Try to close the window (works if opened by script)
    if (window.opener) {
        window.close();
    } else {
        // Navigate back or to delivery notes list
        window.location.href = route('delivery-notes.index');
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist Era
    return `${day}/${month}/${year} พ.ศ.`;
};

const getPricingTypeLabel = (type: string) => {
    const labels = {
        regular: 'ราคาปกติ',
        credit: 'ราคาเครดิต',
    };
    return labels[type as keyof typeof labels] || 'ราคาปกติ';
};

const shouldShowDriverVehicle = () => {
    // Show driver/vehicle info if explicitly set
    return (props.deliveryNote.driver && props.deliveryNote.vehicle);
};

const formatBahtText = (amount: number) => {
    return bahttext(amount);
};

// Auto-print when page loads
import { onMounted } from 'vue';
onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <Head :title="`ใบส่งของ #${deliveryNote.id}`" />

    <!-- Print Controls (hidden in print) -->
    <div class="no-print mb-6 max-w-4xl mx-auto">
        <Card>
            <CardContent class="pt-6">
                <div class="flex flex-col items-center space-y-4">
                    <h2 class="text-lg font-semibold text-center">ตัวอย่างการพิมพ์ใบส่งของ #{{ deliveryNote.id }}</h2>
                    <Separator />
                    <div class="flex items-center space-x-2">
                        <Button @click="handlePrint" class="flex items-center space-x-2">
                            <Printer class="h-4 w-4" />
                            <span>พิมพ์ใบส่งของ</span>
                        </Button>
                        <Button @click="toggleFlip" variant="outline" class="flex items-center space-x-2">
                            <RotateCcw class="h-4 w-4" />
                            <span>{{ isFlipped ? 'แนวตั้ง' : 'แนวนอน' }}</span>
                        </Button>
                        <Button @click="handleClose" variant="ghost" class="flex items-center space-x-2">
                            <X class="h-4 w-4" />
                            <span>ปิด</span>
                        </Button>
                    </div>
                    <p class="text-sm text-muted-foreground text-center">
                        {{ isFlipped ? 'แสดงในแนวนอน (90°)' : 'แสดงในแนวตั้ง' }} • พิมพ์บนกระดาษต่อเนื่อง
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>

    <div class="print-container" :class="{ 'landscape': isFlipped }">

        <!-- Delivery Note Content -->
        <div class="delivery-note" :class="{ 'flipped': isFlipped }">
            <!-- Header -->
            <div class="header-title">ใบส่งของ</div>
            <div class="text-center mb-4">เลขที่: #{{ deliveryNote.id }}</div>

            <!-- Customer Information -->
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <strong>ลูกค้า:</strong> {{ deliveryNote.client.name }}
                    </div>
                    <div class="info-item" v-if="deliveryNote.client.address">
                        <strong>ที่อยู่:</strong> {{ deliveryNote.client.address }}
                    </div>
                    <div class="info-item" v-if="deliveryNote.client.phone">
                        <strong>โทร:</strong> {{ deliveryNote.client.phone }}
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <strong>วันที่ส่ง:</strong> {{ formatDate(deliveryNote.delivery_date) }}
                    </div>
                    <div class="info-item">
                        <strong>ประเภทราคา:</strong> {{ getPricingTypeLabel(deliveryNote.pricing_type) }}
                    </div>
                    <div class="info-item">
                        <strong>คนออกบิล:</strong> {{ deliveryNote.creator.name }}
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%">รายการ</th>
                        <th style="width: 15%">จำนวน</th>
                        <th style="width: 20%">ราคา/หน่วย</th>
                        <th style="width: 25%">รวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in deliveryNote.items" :key="item.id">
                        <td>
                            {{ item.item.name }}
                            <span v-if="item.unit_multiplier > 1">
                                (x{{ item.unit_multiplier }})
                            </span>
                        </td>
                        <td class="text-center">
                            <div v-if="item.quantity_kg">{{ item.quantity_kg }} กก.</div>
                            <div v-if="item.quantity_bags">
                                {{ item.quantity_bags }} กระสอบ
                                <div v-if="item.item && item.item.kg_per_bag_conversion" style="font-size: 10px; color: #666;">
                                    ({{ item.item.kg_per_bag_conversion }} กก./กระสอบ)
                                </div>
                            </div>
                        </td>
                        <td class="text-right">{{ item.unit_price.toLocaleString() }}</td>
                        <td class="text-right">{{ item.total_price.toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Service Fees Table -->
            <div v-if="deliveryNote.service_fee || deliveryNote.bag_fee || deliveryNote.transport_fee" style="margin-top: 15px;">
                <div style="background-color: #f5f5f5; padding: 5px 10px; border: 1px solid #000; border-bottom: none;">
                    <strong style="font-size: 12px;">ค่าบริการเพิ่มเติม</strong>
                </div>
                <table class="items-table" style="margin-top: 0;">
                    <thead>
                        <tr>
                            <th style="width: 40%">รายการ</th>
                            <th style="width: 15%">จำนวน</th>
                            <th style="width: 20%">ราคา/หน่วย</th>
                            <th style="width: 25%">รวม (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="deliveryNote.service_fee">
                            <td>ค่าผสม</td>
                            <td class="text-center">
                                {{ deliveryNote.service_fee_per_ton && deliveryNote.service_fee_per_ton > 0 
                                    ? (deliveryNote.service_fee / deliveryNote.service_fee_per_ton).toFixed(2) + ' ตัน'
                                    : '-' }}
                            </td>
                            <td class="text-right">
                                {{ deliveryNote.service_fee_per_ton ? deliveryNote.service_fee_per_ton.toLocaleString() + ' บาท/ตัน' : '-' }}
                            </td>
                            <td class="text-right">{{ deliveryNote.service_fee.toLocaleString() }}</td>
                        </tr>
                        <tr v-if="deliveryNote.bag_fee">
                            <td>ค่ากระสอบ</td>
                            <td class="text-center">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right">{{ deliveryNote.bag_fee.toLocaleString() }}</td>
                        </tr>
                        <tr v-if="deliveryNote.transport_fee">
                            <td>ค่าขนส่ง</td>
                            <td class="text-center">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right">{{ deliveryNote.transport_fee.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="summary-section">
                <div v-if="deliveryNote.total_weight" class="mb-2">
                    <strong>น้ำหนักรวม:</strong> {{ deliveryNote.total_weight.toLocaleString() }} กิโลกรัม
                </div>
                <div class="mb-2">
                    <strong>รวมค่าสินค้า:</strong> {{ Number(deliveryNote.items.reduce((total, item) => Number(total) + Number(item.total_price), 0)).toLocaleString() }} บาท
                </div>
                <div v-if="deliveryNote.service_fee" class="mb-2">
                    <strong>ค่าผสม:</strong> {{ deliveryNote.service_fee.toLocaleString() }} บาท
                </div>
                <div v-if="deliveryNote.bag_fee" class="mb-2">
                    <strong>ค่ากระสอบ:</strong> {{ deliveryNote.bag_fee.toLocaleString() }} บาท
                </div>
                <div v-if="deliveryNote.transport_fee" class="mb-2">
                    <strong>ค่าขนส่ง:</strong> {{ deliveryNote.transport_fee.toLocaleString() }} บาท
                </div>
                <div class="font-bold" style="font-size: 14px;">
                    <strong>รวมทั้งหมด: {{ deliveryNote.total_amount?.toLocaleString() }} บาท</strong>
                </div>
                <div class="mt-2" style="font-size: 11px;">
                    <strong>จำนวนเงิน (ตัวอักษร): {{ formatBahtText(deliveryNote.total_amount || 0) }}</strong>
                </div>
            </div>

            <!-- Driver and Vehicle Info (conditional) -->
            <div v-if="shouldShowDriverVehicle()" class="mt-4 info-grid">
                <div v-if="deliveryNote.driver">
                    <div class="info-item">
                        <strong>คนขับ:</strong> {{ deliveryNote.driver.name }}
                    </div>
                    <div class="info-item" v-if="deliveryNote.driver.phone">
                        <strong>โทร:</strong> {{ deliveryNote.driver.phone }}
                    </div>
                </div>
                <div v-if="deliveryNote.vehicle">
                    <div class="info-item">
                        <strong>ทะเบียนรถ:</strong> {{ deliveryNote.vehicle.license_plate }}
                    </div>
                    <div class="info-item" v-if="deliveryNote.vehicle.province">
                        <strong>จังหวัด:</strong> {{ deliveryNote.vehicle.province }}
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="deliveryNote.notes" class="mt-4">
                <div class="info-item">
                    <strong>หมายเหตุ:</strong> {{ deliveryNote.notes }}
                </div>
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    ผู้ส่ง
                </div>
                <div class="signature-box">
                    ผู้รับ
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4" style="font-size: 10px; color: #666;">
                วันที่พิมพ์: {{ formatDate(new Date().toISOString()) }}
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        font-family: 'Sarabun', 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        font-size: 12px;
    }
    .print-container {
        width: 210mm; /* Always A4 width */
        height: 148mm; /* Top half of A4 */
        margin: 0;
        padding: 4mm;
        box-sizing: border-box;
    }
    .no-print {
        display: none;
    }
    .page-break {
        page-break-after: always;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #000;
    }
    th, td {
        padding: 4px;
        text-align: left;
        font-size: 11px;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .mb-1 { margin-bottom: 2px; }
    .mb-2 { margin-bottom: 4px; }
    .mb-4 { margin-bottom: 8px; }
    .mt-4 { margin-top: 8px; }
}

@media screen {
    .print-container {
        width: 210mm; /* A4 width */
        height: 148mm; /* Top half of A4 */
        margin: 20px auto;
        padding: 4mm;
        border: 1px solid hsl(var(--border));
        background: white;
        font-family: 'Sarabun', 'Arial', sans-serif;
        font-size: 12px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        border-radius: 8px;
    }
}

.header-title {
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 8px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 12px;
}

.info-item {
    margin-bottom: 4px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.items-table th,
.items-table td {
    border: 1px solid #000;
    padding: 4px;
    font-size: 11px;
}

.items-table th {
    background-color: #f5f5f5;
    font-weight: bold;
    text-align: center;
}

.summary-section {
    margin-top: 12px;
    text-align: right;
}

.signature-section {
    margin-top: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.signature-box {
    text-align: center;
    padding-top: 30px;
    border-top: 1px solid #000;
    margin-top: 20px;
}


.delivery-note {
    transition: transform 0.3s ease;
    transform-origin: center center;
}

.delivery-note.flipped {
    transform: rotate(90deg);
}

@media print {
    .print-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .delivery-note.flipped {
        transform: rotate(90deg);
        width: 140mm; /* Must fit within 148mm container height */
        height: 194mm; /* Must fit within 210mm container width */
        transform-origin: center center;
    }
}

@media screen {
    .print-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .delivery-note.flipped {
        transform: rotate(90deg);
        width: 140mm;
        height: 194mm;
        transform-origin: center center;
    }
}
</style>