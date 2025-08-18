<template>
    <div>
        <div class="flex items-center justify-between">
            <Label class="text-xs font-medium">เลือกวันที่</Label>
            <div v-if="modelValue" class="text-xs text-gray-600">
                {{ new Date(modelValue + 'T00:00:00').toLocaleDateString('th-TH') }}
            </div>
        </div>
        
        <!-- Calendar Widget -->
        <div class="mt-2 p-3 border rounded-lg bg-gray-50">
            <div class="text-xs font-medium text-gray-700 mb-2">เลือกวันที่</div>
            <div class="grid grid-cols-7 gap-1 text-xs">
                <!-- Calendar Header -->
                <div class="text-center font-medium text-gray-500 py-1">อา</div>
                <div class="text-center font-medium text-gray-500 py-1">จ</div>
                <div class="text-center font-medium text-gray-500 py-1">อ</div>
                <div class="text-center font-medium text-gray-500 py-1">พ</div>
                <div class="text-center font-medium text-gray-500 py-1">พฤ</div>
                <div class="text-center font-medium text-gray-500 py-1">ศ</div>
                <div class="text-center font-medium text-gray-500 py-1">ส</div>
                
                <!-- Calendar Days -->
                <template v-for="day in calendarDays" :key="day.key">
                    <button
                        v-if="day.date"
                        @click="selectDate(day.fullDate)"
                        :class="[
                            'text-center py-1 rounded transition-colors',
                            day.isSelected ? 'bg-blue-500 text-white' : 
                            day.isToday ? 'bg-blue-100 text-blue-800 font-medium' :
                            day.isCurrentMonth ? 'hover:bg-gray-200 text-gray-900' :
                            'text-gray-400 hover:bg-gray-100'
                        ]"
                    >
                        {{ day.date }}
                    </button>
                    <div v-else class="py-1"></div>
                </template>
            </div>
            
            <!-- Quick Date Selection -->
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="text-xs text-gray-600 mb-2">เลือกเร็ว:</div>
                <div class="flex gap-1 flex-wrap">
                    <button
                        @click="selectQuickDate('today')"
                        class="px-2 py-1 text-xs bg-white border rounded hover:bg-gray-50"
                    >
                        วันนี้
                    </button>
                    <button
                        @click="selectQuickDate('yesterday')"
                        class="px-2 py-1 text-xs bg-white border rounded hover:bg-gray-50"
                    >
                        เมื่อวาน
                    </button>
                    <button
                        @click="selectQuickDate('week')"
                        class="px-2 py-1 text-xs bg-white border rounded hover:bg-gray-50"
                    >
                        สัปดาห์ที่แล้ว
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Label } from '@/components/ui/label';

interface Props {
    modelValue: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// Calendar state
const currentCalendarDate = ref(new Date());

// Generate calendar days
const calendarDays = computed(() => {
    const year = currentCalendarDate.value.getFullYear();
    const month = currentCalendarDate.value.getMonth();
    
    // First day of the month
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Start from Sunday of the week containing the first day
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - startDate.getDay());
    
    // Generate 6 weeks of days
    const days = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dateStr = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        
        days.push({
            key: `day-${i}`,
            date: date.getDate(),
            fullDate: dateStr,
            isCurrentMonth: date.getMonth() === month,
            isToday: date.getTime() === today.getTime(),
            isSelected: dateStr === props.modelValue
        });
        
        // Stop if we've covered the current month and the week is complete
        if (date > lastDay && date.getDay() === 6) {
            break;
        }
    }
    
    return days;
});

// Select date from calendar
const selectDate = (date: string) => {
    emit('update:modelValue', date);
};

// Quick date selection
const selectQuickDate = (type: 'today' | 'yesterday' | 'week') => {
    const targetDate = new Date();
    
    switch (type) {
        case 'yesterday':
            targetDate.setDate(targetDate.getDate() - 1);
            break;
        case 'week':
            targetDate.setDate(targetDate.getDate() - 7);
            break;
    }
    
    const dateStr = `${targetDate.getFullYear()}-${String(targetDate.getMonth() + 1).padStart(2, '0')}-${String(targetDate.getDate()).padStart(2, '0')}`;
    emit('update:modelValue', dateStr);
    currentCalendarDate.value = targetDate;
};
</script>