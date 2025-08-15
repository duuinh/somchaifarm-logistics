<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    current_page: number;
    data: any[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    data: PaginationData;
}

const props = defineProps<Props>();

const pageNumbers = computed(() => {
    const pages: number[] = [];
    const current = props.data.current_page;
    const last = props.data.last_page;
    
    // Simple approach: show all pages if 7 or less, otherwise show smart pagination
    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        // Always show first page
        pages.push(1);
        
        // Add ellipsis if needed
        if (current > 3) {
            pages.push(-1); // -1 represents ellipsis
        }
        
        // Add pages around current
        const start = Math.max(2, current - 1);
        const end = Math.min(last - 1, current + 1);
        
        for (let i = start; i <= end; i++) {
            if (!pages.includes(i)) {
                pages.push(i);
            }
        }
        
        // Add ellipsis if needed
        if (current < last - 2) {
            pages.push(-1); // -1 represents ellipsis
        }
        
        // Always show last page
        if (last > 1 && !pages.includes(last)) {
            pages.push(last);
        }
    }
    
    return pages;
});

const showingText = computed(() => {
    if (props.data.total === 0) {
        return 'ไม่พบข้อมูล';
    }
    return `แสดง ${props.data.from || 0} ถึง ${props.data.to || 0} จากทั้งหมด ${props.data.total} รายการ`;
});
</script>

<template>
    <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-muted-foreground">
            {{ showingText }}
        </div>
        
        <div v-if="data.last_page > 1" class="flex items-center gap-2">
            <!-- First page -->
            <Link 
                v-if="data.current_page > 1"
                :href="data.first_page_url"
                preserve-scroll
                preserve-state
            >
                <Button variant="outline" size="sm" :disabled="!data.prev_page_url">
                    <ChevronsLeft class="h-4 w-4" />
                </Button>
            </Link>
            <Button v-else variant="outline" size="sm" disabled>
                <ChevronsLeft class="h-4 w-4" />
            </Button>
            
            <!-- Previous page -->
            <Link 
                v-if="data.prev_page_url"
                :href="data.prev_page_url"
                preserve-scroll
                preserve-state
            >
                <Button variant="outline" size="sm">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
            </Link>
            <Button v-else variant="outline" size="sm" disabled>
                <ChevronLeft class="h-4 w-4" />
            </Button>
            
            <!-- Page numbers -->
            <template v-for="page in pageNumbers" :key="page">
                <span v-if="page === -1" class="px-2 text-muted-foreground">...</span>
                <Link
                    v-else-if="page !== data.current_page"
                    :href="`?page=${page}`"
                    preserve-scroll
                    preserve-state
                >
                    <Button variant="outline" size="sm">
                        {{ page }}
                    </Button>
                </Link>
                <Button v-else variant="default" size="sm">
                    {{ page }}
                </Button>
            </template>
            
            <!-- Next page -->
            <Link 
                v-if="data.next_page_url"
                :href="data.next_page_url"
                preserve-scroll
                preserve-state
            >
                <Button variant="outline" size="sm">
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </Link>
            <Button v-else variant="outline" size="sm" disabled>
                <ChevronRight class="h-4 w-4" />
            </Button>
            
            <!-- Last page -->
            <Link 
                v-if="data.current_page < data.last_page"
                :href="data.last_page_url"
                preserve-scroll
                preserve-state
            >
                <Button variant="outline" size="sm">
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </Link>
            <Button v-else variant="outline" size="sm" disabled>
                <ChevronsRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>