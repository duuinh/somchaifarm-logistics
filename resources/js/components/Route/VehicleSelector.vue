<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between mb-2">
            <Label class="text-xs font-medium">เลือกรถ</Label>
            <div class="flex gap-1">
                <button
                    @click="selectAll"
                    class="text-xs text-blue-600 hover:text-blue-800"
                >
                    เลือกทั้งหมด
                </button>
                <span class="text-xs text-gray-400">|</span>
                <button
                    @click="clearSelection"
                    class="text-xs text-red-600 hover:text-red-800"
                >
                    ล้าง
                </button>
            </div>
        </div>
        <div class="border rounded-md p-2 bg-gray-50 max-h-28 overflow-y-auto">
            <div class="space-y-2">
                <div v-for="group in devicesByType" :key="group.type" class="space-y-1">
                    <div class="flex items-center gap-2 sticky top-0 bg-gray-50">
                        <input
                            type="checkbox"
                            :id="`group-${group.type}`"
                            :checked="isGroupSelected(group.type)"
                            @change="toggleGroup(group.type)"
                            class="rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <label :for="`group-${group.type}`" class="text-xs font-medium text-gray-700 cursor-pointer">
                            {{ group.type }} ({{ group.vehicles.length }} คัน)
                        </label>
                    </div>
                    <div class="ml-4 space-y-1">
                        <div v-for="vehicle in group.vehicles" :key="vehicle.id" class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                :id="`vehicle-${vehicle.id}`"
                                :value="vehicle.id"
                                :checked="modelValue.includes(vehicle.id)"
                                @change="toggleVehicle(vehicle.id)"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <div
                                v-if="modelValue.includes(vehicle.id)"
                                class="w-2 h-2 rounded-full"
                                :style="{ backgroundColor: getVehicleColor(vehicle.id) }"
                            ></div>
                            <label :for="`vehicle-${vehicle.id}`" class="text-xs text-gray-600 cursor-pointer truncate flex-1" :title="vehicle.name">
                                {{ vehicle.name.replace(/^🚛\s*|^🚚\s*/, '').replace(/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu, '').trim() }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="modelValue.length > 0" class="text-xs text-gray-600 mt-1">
            เลือกแล้ว {{ modelValue.length }}/{{ devices.length }} คัน
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, watch } from 'vue';
import { Label } from '@/components/ui/label';

interface Vehicle {
    id: number;
    name: string;
    type: string;
    category: string;
}

interface Props {
    modelValue: number[];
    devices: Vehicle[];
    vehicleColors: string[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: number[]];
}>();

// Group devices by type
const devicesByType = computed(() => {
    const grouped = props.devices.reduce((acc, device) => {
        if (!acc[device.type]) {
            acc[device.type] = [];
        }
        acc[device.type].push(device);
        return acc;
    }, {} as Record<string, typeof props.devices>);
    
    const typeOrder = ['รถพ่วง', '10 ล้อ', '6 ล้อ', 'รถกระบะ'];
    const orderedGroups: Array<{ type: string; vehicles: typeof props.devices }> = [];
    
    typeOrder.forEach(type => {
        if (grouped[type]) {
            orderedGroups.push({
                type,
                vehicles: grouped[type].sort((a, b) => a.name.localeCompare(b.name))
            });
        }
    });
    
    return orderedGroups;
});

// Get vehicle color
const getVehicleColor = (deviceId: number) => {
    const index = props.modelValue.indexOf(deviceId);
    if (index >= 0 && props.vehicleColors.length > 0) {
        return props.vehicleColors[index % props.vehicleColors.length];
    }
    return '#0000FF'; // Default blue color
};

// Check if group is selected
const isGroupSelected = (type: string) => {
    const group = devicesByType.value.find(g => g.type === type);
    if (!group) return false;
    return group.vehicles.every(v => props.modelValue.includes(v.id));
};

// Check if group is indeterminate
const isGroupIndeterminate = (type: string) => {
    const group = devicesByType.value.find(g => g.type === type);
    if (!group) return false;
    const selectedCount = group.vehicles.filter(v => props.modelValue.includes(v.id)).length;
    return selectedCount > 0 && selectedCount < group.vehicles.length;
};

// Toggle group selection
const toggleGroup = (type: string) => {
    const group = devicesByType.value.find(g => g.type === type);
    if (!group) return;
    
    const isSelected = isGroupSelected(type);
    const newSelection = [...props.modelValue];
    
    if (isSelected) {
        // Remove all vehicles from this group
        group.vehicles.forEach(v => {
            const index = newSelection.indexOf(v.id);
            if (index > -1) newSelection.splice(index, 1);
        });
    } else {
        // Add all vehicles from this group
        group.vehicles.forEach(v => {
            if (!newSelection.includes(v.id)) {
                newSelection.push(v.id);
            }
        });
    }
    
    emit('update:modelValue', newSelection);
};

// Toggle individual vehicle
const toggleVehicle = (vehicleId: number) => {
    const newSelection = [...props.modelValue];
    const index = newSelection.indexOf(vehicleId);
    
    if (index > -1) {
        newSelection.splice(index, 1);
    } else {
        newSelection.push(vehicleId);
    }
    
    emit('update:modelValue', newSelection);
};

// Select all vehicles
const selectAll = () => {
    emit('update:modelValue', props.devices.map(d => d.id));
};

// Clear selection
const clearSelection = () => {
    emit('update:modelValue', []);
};

// Update indeterminate states for group checkboxes
const updateIndeterminateStates = () => {
    nextTick(() => {
        devicesByType.value.forEach(group => {
            const checkbox = document.getElementById(`group-${group.type}`) as HTMLInputElement;
            if (checkbox) {
                checkbox.indeterminate = isGroupIndeterminate(group.type);
            }
        });
    });
};

// Watch for selection changes to update indeterminate states
watch(() => props.modelValue, updateIndeterminateStates, { immediate: true });

// Update indeterminate states on mount
onMounted(updateIndeterminateStates);
</script>