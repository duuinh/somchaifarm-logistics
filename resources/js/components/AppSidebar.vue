<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid, FileText, Users, Package, Truck, IdCard, Settings } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

const mainNavItems: NavItem[] = [
    {
        title: 'แดชบอร์ด',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'ใบส่งของ',
        href: '/delivery-notes',
        icon: FileText,
    },
    {
        title: 'จัดการลูกค้า',
        href: '/clients',
        icon: Users,
    },
    {
        title: 'จัดการสินค้า',
        href: '/items',
        icon: Package,
    },
    {
        title: 'จัดการคนขับ',
        href: '/drivers',
        icon: IdCard,
    },
    {
        title: 'จัดการรถ',
        href: '/vehicles',
        icon: Truck,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'การตั้งค่าธุรกิจ',
        href: '/business-settings',
        icon: Settings,
    },
    {
        title: 'จัดการยูเซอร์',
        href: '/users',
        icon: Users,
    },
    {
        title: 'คู่มือการใช้งาน',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
