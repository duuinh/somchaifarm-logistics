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
    quantity: number;
    unit_type: 'kg' | 'bags';
    price_per_unit: number;
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

interface CompanySettings {
    company_name: string;
    company_address: string;
    company_phone: string;
    company_tax_id: string;
    company_fax?: string;
}

interface Props {
    deliveryNote: DeliveryNote;
    companySettings: CompanySettings;
}

const props = defineProps<Props>();

const isFlipped = ref(true);

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
    return `${day}/${month}/${year}`;
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

import { onMounted } from 'vue';

// Auto-print when page loads
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
            <div class="text-center mb-2">
                <div class="flex items-center justify-center gap-4 mb-1">
                    <img src="/images/logo.png" alt="Company Logo" class="w-20 h-20 object-contain" />
                    <div class="text-left" v-if="companySettings?.company_name">
                        <div class="text-sm font-bold mb-px">{{ companySettings.company_name }}</div>
                        <div class="text-xs mb-px" v-if="companySettings.company_address">{{ companySettings.company_address }}</div>
                        <div class="text-xs mb-px">
                            <span v-if="companySettings.company_phone">โทร. {{ companySettings.company_phone }}</span>
                            <span v-if="companySettings.company_fax"> แฟกซ์ {{ companySettings.company_fax }}</span>
                        </div>
                        <div class="text-xs" v-if="companySettings.company_tax_id">
                            เลขประจำตัวผู้เสียภาษีอากร {{ companySettings.company_tax_id }}
                        </div>
                    </div>
                </div>

                <div class="relative grid grid-cols-3 items-center mb-1">
                    <div></div>
                    <div class="text-base font-bold">ใบส่งสินค้า</div>
                    <div class="text-sm text-right">เลขที่ {{ deliveryNote.id }}</div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="mb-2">
                <div class="flex justify-end text-xs mb-1">
                    <span>วันที่ {{ formatDate(deliveryNote.created_at) }}</span>
                </div>
                <div class="flex items-baseline text-xs mb-1">
                    <span class="min-w-[50px]">นาม </span>
                    <span class="dots flex-dots">{{ deliveryNote.client.name }}</span>
                </div>
                <div class="flex items-baseline text-xs mb-1">
                    <span class="min-w-[50px]">ที่อยู่ </span>
                    <span class="dots flex-dots">{{ deliveryNote.client.address || '' }}</span>
                </div>
                <div class="flex items-baseline text-xs mt-1">
                    <span class="text-sm mx-1">☐</span> สำนักงานใหญ่
                    <span class="flex items-baseline mx-2">
                        <span class="text-sm ml-1 mr-1">☐</span> สาขา <span class="dots short ml-1"></span>
                    </span>
                    <span class="flex items-baseline flex-1 ml-2 text-xs">
                        เลขที่ผู้เสียภาษี <span class="dots flex-dots">{{ deliveryNote.client.tax_id || '' }}</span>
                    </span>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 15%">จำนวน</th>
                        <th style="width: 40%">รายการสินค้า</th>
                        <th style="width: 20%">หน่วยละ</th>
                        <th style="width: 25%">จำนวนเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in deliveryNote.items" :key="item.id">
                        <td class="text-center">
                            <div>{{ item.quantity }} {{ item.unit_type === 'kg' ? 'กก.' : 'กระสอบ' }}</div>
                        </td>
                        <td>
                            {{ item.item.name }}
                        </td>
                        <td class="text-right">{{ item.price_per_unit.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                        <td class="text-right">{{ item.total_price.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                    </tr>

                    <!-- Service fees rows integrated into items table -->
                    <tr v-if="deliveryNote.service_fee">
                        <td class="text-center">
                            {{ deliveryNote.service_fee_per_ton && deliveryNote.service_fee_per_ton > 0
                                ? (deliveryNote.service_fee / deliveryNote.service_fee_per_ton) + ' ตัน'
                                : '-' }}
                        </td>
                        <td>ค่าผสมอาหาร</td>
                        <td class="text-right">
                            {{ deliveryNote.service_fee_per_ton ? deliveryNote.service_fee_per_ton.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-' }}
                        </td>
                        <td class="text-right">{{ deliveryNote.service_fee.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                    </tr>
                    <tr v-if="deliveryNote.bag_fee">
                        <td class="text-center">-</td>
                        <td>ค่ากระสอบ</td>
                        <td class="text-right">-</td>
                        <td class="text-right">{{ deliveryNote.bag_fee.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                    </tr>
                    <tr v-if="deliveryNote.transport_fee">
                        <td class="text-center">-</td>
                        <td>ค่าขนส่ง</td>
                        <td class="text-right">-</td>
                        <td class="text-right">{{ deliveryNote.transport_fee.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                    </tr>

                    <!-- Summary rows -->
                    <tr class="border-t-2 border-black">
                        <td colspan="2" class="text-center p-1">
                            <div class="font-bold text-xs">{{ formatBahtText(deliveryNote.total_amount || 0) }}</div>
                            <div class="text-[9px] text-gray-600">จำนวนเงินรวมทั้งสิ้น (ตัวอักษร)</div>
                        </td>
                        <td class="text-right font-bold">รวมเงิน</td>
                        <td class="text-right font-bold">{{ deliveryNote.total_amount?.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                    </tr>
                </tbody>
            </table>


            <!-- Notes -->
            <div v-if="deliveryNote.notes" class="mt-1">
                <div class="text-xs mb-1">
                    <div class="flex">
                        <strong class="mr-1">หมายเหตุ:</strong>
                        <div class="flex-1 notes-content">
                            <div v-for="(line, index) in deliveryNote.notes.split('\n')" :key="index" class="notes-line">
                                {{ line }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="p-1">
                    <div class="flex items-baseline mb-1 text-xs">
                        <span class="mr-2 min-w-[60px]">ผู้รับสินค้า</span>
                        <span class="dots flex-1 min-w-[120px] pb-0.5"></span>
                    </div>
                    <div class="flex items-baseline mb-1 text-xs">
                        <span class="mr-2 min-w-[60px]">วันที่</span>
                        <span class="dots flex-1 min-w-[120px] pb-0.5"></span>
                    </div>
                </div>
                <div class="p-1">
                    <div class="flex items-baseline mb-1 text-xs">
                        <span class="mr-2 min-w-[60px]">ผู้ส่งสินค้า</span>
                        <span class="dots flex-1 min-w-[120px] pb-0.5"></span>
                    </div>
                    <div class="flex items-baseline mb-1 text-xs">
                        <span class="mr-2 min-w-[60px]">วันที่</span>
                        <span class="dots flex-1 min-w-[120px] pb-0.5">{{ formatDate(deliveryNote.delivery_date) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style>
/* ==================== Media Queries ==================== */
@media print {
    body {
        font-family: 'Sarabun', 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        font-size: 12px;
    }
    
    .print-container {
        width: 210mm;
        height: 148mm;
        margin: 0;
        padding: 5mm;
        box-sizing: border-box;
    }
    
    
    .no-print {
        display: none;
    }
    
    /* Table print styles */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    table, th, td {
        border: 1px solid #000;
    }
    
    th, td {
        padding: 3px;
        text-align: left;
        font-size: 11px;
    }
}

@media screen {
    .print-container {
        width: 210mm;
        height: 148mm;
        margin: 20px auto;
        padding: 5mm;
        border: 1px solid hsl(var(--border));
        background: white;
        font-family: 'Sarabun', 'Arial', sans-serif;
        font-size: 12px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        border-radius: 8px;
        box-sizing: border-box;
    }
    
}

/* ==================== Print-specific styles ==================== */

/* ==================== Table Section ==================== */
.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
}

.items-table th,
.items-table td {
    border: 1px solid #000;
    padding: 3px;
    font-size: 11px;
}

.items-table th {
    background-color: #f5f5f5;
    font-weight: bold;
    text-align: center;
}

/* ==================== Signature Section ==================== */
.signature-section {
    position: absolute;
    bottom: 10px;
    left: 0;
    right: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    padding: 0 10px;
}

/* ==================== Dotted Line Styles ==================== */
.dots {
    display: inline-block;
    border-bottom: 1px dotted #000;
    min-height: 1em;
    vertical-align: bottom;
}

.dots.short {
    min-width: 80px;
    flex: 1;
}

.dots.medium {
    min-width: 150px;
}

.dots.flex-dots {
    flex: 1;
    margin-left: 5px;
    padding-bottom: 1px;
}

/* ==================== Layout Container ==================== */
.delivery-note {
    position: relative;
    width: 138mm;
    min-height: 128mm;
    padding: 5mm;
    transition: transform 0.3s ease;
    transform-origin: center center;
    box-sizing: border-box;
}

.delivery-note.flipped {
    transform: rotate(90deg);
}

/* Custom utility classes not covered by Tailwind */

/* ==================== Notes Formatting ==================== */
.notes-line {
    margin-left: 0;
}

/* ==================== Rotation Support ==================== */
@media print {
    .print-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .delivery-note.flipped {
        transform: rotate(90deg);
        width: 138mm;
        height: 190mm;
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
        width: 138mm;
        height: 190mm;
        transform-origin: center center;
    }
}
</style>