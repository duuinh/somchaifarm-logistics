<template>
    <div class="space-y-1">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>แคช: {{ cacheStats.indexedDB.count }} รายการ</span>
            <div class="flex gap-1">
                <Button
                    @click="$emit('update-stats')"
                    variant="ghost"
                    size="sm"
                    class="text-blue-600 hover:text-blue-700 h-5 p-1 text-xs"
                    title="รีเฟรชข้อมูลแคช"
                >
                    <RefreshCw class="h-2 w-2" />
                </Button>
                <Button
                    @click="$emit('clear-cache')"
                    variant="ghost"
                    size="sm"
                    class="text-red-600 hover:text-red-700 h-5 p-1 text-xs"
                    title="ล้างข้อมูลแคชทั้งหมด"
                >
                    <Database class="h-2 w-2 mr-1" />
                    ล้าง
                </Button>
            </div>
        </div>
        
        <!-- IndexedDB Stats -->
        <div v-if="cacheInitialized" class="text-xs space-y-1">
            <div class="text-green-600 font-medium" title="ข้อมูลเส้นทางที่เก็บไว้ในเครื่องเพื่อความเร็ว">
                📦 แคชในเครื่อง: {{ cacheStats.indexedDB.count }} รายการ, {{ formatFileSize(cacheStats.indexedDB.estimatedSize) }}
            </div>
        </div>
        
        <!-- Cache Error Warning -->
        <div v-if="cacheError" class="text-xs text-red-600 bg-red-50 p-1 rounded" title="ข้อผิดพลาดในการใช้งานแคช">
            ⚠️ {{ cacheError }}
        </div>
        
        <!-- Cache Status -->
        <div v-if="!cacheInitialized" class="text-xs text-orange-600 bg-orange-50 p-1 rounded" title="กำลังเตรียมระบบแคชในเครื่อง">
            ⏳ กำลังเริ่มต้นแคช...
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { RefreshCw, Database } from 'lucide-vue-next';

interface Props {
    cacheStats: {
        indexedDB: {
            count: number;
            estimatedSize: number;
            oldestEntry: string | null;
            newestEntry: string | null;
        };
    };
    cacheInitialized: boolean;
    cacheError: string | null;
}

defineProps<Props>();

defineEmits<{
    'update-stats': [];
    'clear-cache': [];
}>();

// Format file size in human readable format
const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

// Format date in Thai Buddhist calendar
const formatThaiDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        day: 'numeric',
        month: 'numeric', 
        year: 'numeric'
    });
};
</script>