// Shared types for Delivery Notes
export interface Client {
    id: number;
    name: string;
    address?: string;
    phone?: string;
}

export interface Item {
    id: number;
    name: string;
    regular_price_per_kg?: number;
    regular_price_per_bag?: number;
    credit_price_per_kg?: number;
    credit_price_per_bag?: number;
    kg_per_bag_conversion: number;
}

export interface Driver {
    id: number;
    name: string;
    phone?: string;
}

export interface Vehicle {
    id: number;
    license_plate: string;
    province?: string;
    vehicle_type?: string;
    load_capacity?: number;
}

export interface DeliveryNoteItem {
    id?: number;
    item_id: number;
    quantity_kg?: number;
    quantity_bags?: number;
    unit_multiplier: number;
    unit_price: number;
    total_price: number;
    item?: Item;
}

export interface DeliveryNote {
    id: number;
    client_id: number;
    driver_id?: number;
    vehicle_id?: number;
    delivery_date: string;
    pricing_type: 'regular' | 'credit';
    total_weight?: number;
    total_amount?: number;
    service_fee?: number;
    service_fee_per_ton?: number;
    bag_fee?: number;
    transport_fee?: number;
    notes?: string;
    items: DeliveryNoteItem[];
    created_at?: string;
}

export type UnitType = 'kg' | 'bags';
export type PricingType = 'regular' | 'credit';

export interface ServiceFeeConfig {
    includeServiceFee: boolean;
    includeBagFee: boolean;
    includeTransportFee: boolean;
}

export interface DeliveryNoteFormData {
    client_id: string;
    driver_id: string;
    vehicle_id: string;
    delivery_date: string;
    pricing_type: PricingType;
    notes: string;
    service_fee: number;
    service_fee_tons: number;
    service_fee_per_ton: number;
    bag_fee: number;
    transport_fee: number;
    items: DeliveryNoteItem[];
}