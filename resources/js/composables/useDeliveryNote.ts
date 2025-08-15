import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { bahttext } from 'bahttext';
import type { 
    Item, 
    DeliveryNote, 
    DeliveryNoteItem, 
    DeliveryNoteFormData, 
    PricingType, 
    UnitType,
    ServiceFeeConfig,
    Client
} from '@/types/delivery-notes';

export function useDeliveryNote(
    props: {
        items: Item[];
        clients: Client[];
        defaultServiceFee: number;
        deliveryNote?: DeliveryNote;
    }
) {
    // Form initialization
    const initializeForm = (): DeliveryNoteFormData => {
        if (props.deliveryNote) {
            // Edit mode
            return {
                client_id: props.deliveryNote.client_id.toString(),
                driver_id: props.deliveryNote.driver_id?.toString() || '',
                vehicle_id: props.deliveryNote.vehicle_id?.toString() || '',
                delivery_date: new Date(props.deliveryNote.delivery_date).toISOString().split('T')[0], // Format as YYYY-MM-DD
                pricing_type: props.deliveryNote.pricing_type,
                notes: props.deliveryNote.notes || '',
                service_fee: props.deliveryNote.service_fee || props.defaultServiceFee,
                service_fee_tons: props.deliveryNote.service_fee_per_ton ? 
                    (props.deliveryNote.service_fee || 0) / (props.deliveryNote.service_fee_per_ton || props.defaultServiceFee) : 0,
                service_fee_per_ton: props.deliveryNote.service_fee_per_ton || props.defaultServiceFee,
                bag_fee: props.deliveryNote.bag_fee || 0,
                transport_fee: props.deliveryNote.transport_fee || 0,
                items: props.deliveryNote.items.map(item => ({
                    id: item.id,
                    item_id: item.item_id,
                    quantity: item.quantity,
                    unit_type: item.unit_type,
                    price_per_unit: Number(item.price_per_unit) || 0,
                    total_price: Number(item.total_price) || 0,
                })),
            };
        } else {
            // Create mode
            return {
                client_id: '',
                driver_id: '',
                vehicle_id: '',
                delivery_date: new Date().toISOString().split('T')[0],
                pricing_type: 'regular',
                notes: '',
                service_fee: props.defaultServiceFee,
                service_fee_tons: 0,
                service_fee_per_ton: props.defaultServiceFee,
                bag_fee: 0,
                transport_fee: 0,
                items: [],
            };
        }
    };

    const form = useForm(initializeForm());

    // Reactive state
    const selectedClient = ref<Client | null>(null);
    const newItemId = ref('');
    const newUnitType = ref<UnitType>('kg');
    const newQuantity = ref<number | null>(null);
    const newUnitPrice = ref<number | null>(null);

    // Service fees checkboxes
    const includeServiceFee = ref(false);
    const includeBagFee = ref(false);
    const includeTransportFee = ref(false);

    // Initialize checkboxes for edit mode
    const initializeServiceFeeCheckboxes = () => {
        if (props.deliveryNote) {
            includeServiceFee.value = !!(props.deliveryNote.service_fee && props.deliveryNote.service_fee > 0);
            includeBagFee.value = !!(props.deliveryNote.bag_fee && props.deliveryNote.bag_fee > 0);
            includeTransportFee.value = !!(props.deliveryNote.transport_fee && props.deliveryNote.transport_fee > 0);
        }
    };

    // Computed properties
    const calculatedServiceFee = computed(() => {
        if (!includeServiceFee.value) return 0;
        return (form.service_fee_tons || 0) * (form.service_fee_per_ton || 0);
    });

    const totalWeight = computed(() => {
        if (!form.items || !Array.isArray(form.items)) return 0;
        const weight = form.items.reduce((total, item) => {
            if (!item) return total;
            if (item.unit_type === 'kg') {
                return total + Number(item.quantity || 0);
            } else if (item.unit_type === 'bags') {
                return total + Number(item.quantity || 0) * getItemConversion(item.item_id);
            }
            return total;
        }, 0);
        return Number(weight);
    });

    const totalAmount = computed(() => {
        const itemsTotal = form.items?.reduce((total, item) => total + (item.total_price || 0), 0) || 0;
        const serviceFee = includeServiceFee.value ? calculatedServiceFee.value : 0;
        const bagFee = includeBagFee.value ? (form.bag_fee || 0) : 0;
        const transportFee = includeTransportFee.value ? (form.transport_fee || 0) : 0;
        return itemsTotal + serviceFee + bagFee + transportFee;
    });

    // Utility functions
    const getItemName = (itemId: number): string => {
        const item = props.items?.find(i => i.id === itemId);
        return item?.name || 'Unknown Item';
    };

    const getItemConversion = (itemId: number): number => {
        const item = props.items?.find(i => i.id === itemId);
        return item?.kg_per_bag_conversion || 0;
    };

    const getSelectedItemInfo = (): Item | null => {
        if (!newItemId.value || !props.items) return null;
        return props.items.find(i => i.id.toString() === newItemId.value.toString()) || null;
    };

    const calculateItemPrice = (item: Item, quantity: number, unitType: UnitType): number => {
        const pricingType = form.pricing_type;
        
        if (unitType === 'kg') {
            const pricePerKg = pricingType === 'credit' ? item.credit_price_per_kg : item.regular_price_per_kg;
            return (pricePerKg || 0) * quantity;
        } else {
            const pricePerBag = pricingType === 'credit' ? item.credit_price_per_bag : item.regular_price_per_bag;
            return (pricePerBag || 0) * quantity;
        }
    };

    const formatBahtText = (amount: number): string => {
        try {
            return bahttext(amount);
        } catch (error) {
            console.error('Error formatting baht text:', error);
            return '';
        }
    };

    // Form actions
    const addItem = () => {
        if (!newItemId.value || !newQuantity.value || newUnitPrice.value === null) {
            return;
        }

        const item = props.items?.find(i => i.id.toString() === newItemId.value.toString());
        if (!item) return;

        const totalPrice = newQuantity.value * newUnitPrice.value;

        const newItem: DeliveryNoteItem = {
            item_id: item.id,
            quantity: newQuantity.value,
            unit_type: newUnitType.value,
            price_per_unit: newUnitPrice.value,
            total_price: totalPrice,
        };

        form.items.push(newItem);
        
        // Reset form
        resetAddItemForm();
    };

    const removeItem = (index: number) => {
        form.items.splice(index, 1);
    };

    const resetAddItemForm = () => {
        newItemId.value = '';
        newUnitType.value = 'kg';
        newQuantity.value = null;
        newUnitPrice.value = null;
    };

    // Watchers
    watch(() => form.client_id, (clientId) => {
        const client = props.clients?.find(c => c.id.toString() === clientId);
        if (client) {
            selectedClient.value = client;
            form.pricing_type = 'regular';
        }
    });

    watch([() => newItemId.value, () => newUnitType.value, () => form.pricing_type], () => {
        if (!newItemId.value || !props.items) return;
        
        const item = props.items.find(i => i.id.toString() === newItemId.value.toString());
        if (!item) return;

        const pricingType = form.pricing_type;
        if (newUnitType.value === 'kg') {
            const price = pricingType === 'credit' ? item.credit_price_per_kg : item.regular_price_per_kg;
            newUnitPrice.value = price ? parseFloat(String(price)) : null;
        } else {
            const price = pricingType === 'credit' ? item.credit_price_per_bag : item.regular_price_per_bag;
            newUnitPrice.value = price ? parseFloat(String(price)) : null;
        }
    });

    watch(() => form.pricing_type, () => {
        if (!form.items || !props.items) return;
        
        form.items = form.items.map(formItem => {
            const item = props.items.find(i => i.id === formItem.item_id);
            if (!item) return formItem;
            
            const totalPrice = calculateItemPrice(item, formItem.quantity, formItem.unit_type);
            const pricePerUnit = totalPrice / formItem.quantity;
            
            return {
                ...formItem,
                price_per_unit: Number(pricePerUnit) || 0,
                total_price: Number(totalPrice) || 0,
            };
        });
    });

    // Update form service fee based on checkbox states
    watch([includeServiceFee, includeBagFee, includeTransportFee], () => {
        if (!includeServiceFee.value) {
            form.service_fee_tons = 0;
        }
        if (!includeBagFee.value) {
            form.bag_fee = 0;
        }
        if (!includeTransportFee.value) {
            form.transport_fee = 0;
        }
    });

    // Update service_fee in form when calculated value changes
    watch(calculatedServiceFee, (newValue) => {
        form.service_fee = newValue;
    });

    return {
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
    };
}