<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    GraduationCap,
    Users,
    School,
    UserPlus,
    CreditCard,
    FileText,
    ClipboardList,
    BarChart3,
    Receipt,
    Award,
    LayoutGrid,
    Settings
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Manage Schools',
        href: '/schools',
        icon: Building2,
    },
    {
        title: 'Manage Classes & Sections',
        href: '/manage/classes-sections',
        icon: School, // or Layers, or another relevant icon
    },
    {
        title: 'Manage Students',
        href: '/students',
        icon: GraduationCap,
    },
    {
        title: 'Manage Teachers',
        href: '/teachers',
        icon: Users,
    },
    {
        title: 'Admissions',
        href: '/admissions',
        icon: UserPlus,
    },
    {
        title: 'Manage Fees',
        href: '/fees',
        icon: CreditCard,
    },
    {
        title: 'Create Papers',
        href: '/papers',
        icon: FileText,
    },
    // {
    //     title: 'Exams',
    //     href: '/exams',
    //     icon: ClipboardList,
    // },
    {
        title: 'Manage Results',
        href: '/results',
        icon: BarChart3,
    },
    {
        title: 'Manage Reports',
        href: '/reports',
        icon: BarChart3,
    },
    {
        title: 'Manage Installments',
        href: '/installments',
        icon: Receipt,
    },
    {
        title: 'Manage Certificates',
        href: '/certificates',
        icon: Award,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="bg-white dark:bg-black">
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
            <SidebarGroup class="px-2 py-0">
                <NavFooter :items="footerNavItems" />
            </SidebarGroup>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
