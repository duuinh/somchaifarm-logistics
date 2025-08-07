<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Item {
    id: number;
    name: string;
    item_type: 'material' | 'service';
}

interface DeliveryNoteItem {
    id: number;
    quantity_kg?: number;
    quantity_bags?: number;
    quantity_units?: number;
    unit_multiplier: number;
    unit_price: number;
    total_price: number;
    item_type: 'material' | 'service';
    item: Item;
}

interface Client {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    customer_type: 'regular' | 'credit' | 'special';
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getPricingTypeLabel = (type: string) => {
    const labels = {
        regular: 'ราคาปกติ',
        credit: 'ราคาเครดิต',
    };
    return labels[type as keyof typeof labels] || 'ราคาปกติ';
};

const shouldShowDriverVehicle = () => {
    // Show driver/vehicle info for special customers or if explicitly set
    return props.deliveryNote.client.customer_type === 'special' || 
           (props.deliveryNote.driver && props.deliveryNote.vehicle);
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

    <div class="print-container">
        <!-- Print Styles -->
        <style>
            @media print {
                body {
                    font-family: 'Sarabun', 'Arial', sans-serif;
                    margin: 0;
                    padding: 0;
                    font-size: 12px;
                }
                .print-container {
                    width: 148mm; /* Half A4 width */
                    height: 210mm; /* A4 height */
                    margin: 0;
                    padding: 8mm;
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
                    max-width: 148mm;
                    margin: 20px auto;
                    padding: 8mm;
                    border: 1px solid #ccc;
                    background: white;
                    font-family: 'Sarabun', 'Arial', sans-serif;
                    font-size: 12px;
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
        </style>

        <!-- Print Button (hidden in print) -->
        <div class="no-print mb-4 text-center">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                พิมพ์ใบส่งของ
            </button>
            <button onclick="window.close()" class="ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                ปิด
            </button>
        </div>

        <!-- Delivery Note Content -->
        <div class="delivery-note">
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
                            <span v-if="item.item_type === 'service' && item.unit_multiplier > 1">
                                (x{{ item.unit_multiplier }})
                            </span>
                        </td>
                        <td class="text-center">
                            <div v-if="item.quantity_kg">{{ item.quantity_kg }} กก.</div>
                            <div v-if="item.quantity_bags">{{ item.quantity_bags }} ถุง</div>
                            <div v-if="item.quantity_units">{{ item.quantity_units }} หน่วย</div>
                        </td>
                        <td class="text-right">{{ item.unit_price.toLocaleString() }}</td>
                        <td class="text-right">{{ item.total_price.toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary -->
            <div class="summary-section">
                <div v-if="deliveryNote.total_weight" class="mb-2">
                    <strong>น้ำหนักรวม:</strong> {{ deliveryNote.total_weight.toLocaleString() }} กิโลกรัม
                </div>
                <div class="font-bold" style="font-size: 14px;">
                    <strong>รวมทั้งหมด: {{ deliveryNote.total_amount?.toLocaleString() }} บาท</strong>
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