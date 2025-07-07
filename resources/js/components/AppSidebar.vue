<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Schools',
        href: '/schools',
        icon: Folder,
    },
    {
        title: 'Students',
        href: '/students',
        icon: Folder,
    },
    {
        title: 'Classes',
        href: '/classes',
        icon: Folder,
    },
    {
        title: 'Teachers',
        href: '/teachers',
        icon: Folder,
    },
    {
        title: 'Admissions',
        href: '/admissions',
        icon: Folder,
    },
    {
        title: 'Fees',
        href: '/fees',
        icon: Folder,
    },
    {
        title: 'Papers',
        href: '/papers',
        icon: Folder,
    },
    {
        title: 'Exams',
        href: '/exams',
        icon: Folder,
    },
    {
        title: 'Results',
        href: '/results',
        icon: Folder,
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: Folder,
    },
    {
        title: 'Installments',
        href: '/installments',
        icon: Folder,
    },
    {
        title: 'Certificates',
        href: '/certificates',
        icon: Folder,
    },
];

const footerNavItems: NavItem[] = [];
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
