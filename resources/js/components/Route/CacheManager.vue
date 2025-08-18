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
                >
                    <RefreshCw class="h-2 w-2" />
                </Button>
                <Button
                    @click="$emit('clear-cache')"
                    variant="ghost"
                    size="sm"
                    class="text-red-600 hover:text-red-700 h-5 p-1 text-xs"
                >
                    <Database class="h-2 w-2 mr-1" />
                    ล้าง
                </Button>
            </div>
        </div>
        
        <!-- IndexedDB Stats -->
        <div v-if="cacheInitialized" class="text-xs space-y-1">
            <div class="text-green-600 font-medium">
                📦 IndexedDB: {{ cacheStats.indexedDB.count }} รายการ, {{ cacheStats.indexedDB.estimatedSize }}KB
            </div>
            <div v-if="cacheStats.indexedDB.oldestEntry" class="text-gray-400">
                เก่าสุด: {{ new Date(cacheStats.indexedDB.oldestEntry).toLocaleDateString('th-TH') }}
            </div>
        </div>
        
        <!-- Cache Error Warning -->
        <div v-if="cacheError" class="text-xs text-red-600 bg-red-50 p-1 rounded">
            ⚠️ {{ cacheError }}
        </div>
        
        <!-- Cache Status -->
        <div v-if="!cacheInitialized" class="text-xs text-orange-600 bg-orange-50 p-1 rounded">
            ⚠️ กำลังเริ่มต้นแคช IndexedDB...
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
    cacheError: string;
}

defineProps<Props>();

defineEmits<{
    'update-stats': [];
    'clear-cache': [];
}>();
</script>