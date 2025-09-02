<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();
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
    Settings,
    BookOpenCheck,
    CalendarCheck
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
        permission: 'read-schools',

    },
    {
        title: 'Manage Classes & Sections',
        href: '/manage/classes-sections',
        icon: School, // or Layers, or another relevant icon
        permission: 'read-classes',

    },
    {
        title: 'Manage Subjects',
        href: '/subjects',
        icon: BookOpenCheck,
        permission: 'read-subjects',

    },
    {
        title: 'Manage Students',
        href: '/students',
        icon: GraduationCap,
        permission: 'read-students',

    },
    {
        title: 'Manage Teachers',
        href: '/teachers',
        icon: Users,
        permission: 'read-teachers',

    },

    {
        title: 'Admissions',
        href: '/admissions',
        icon: UserPlus,
        permission: 'read-admissions',

    },
    {
        title: 'Manage Fees',
        href: '/fees',
        icon: CreditCard,
        permission: 'read-fees',
    },
    {
        title: 'Manage Papers',
        href: '/papersquestions',
        icon: FileText,
        permission: 'read-papers',
    },
    {
        title: 'Attendance Management',
        href: '/attendance',
        icon: CalendarCheck,
        permission: 'read-attendance',
    },
    {
        title: 'Manage Results',
        href: '/results',
        icon: BarChart3,
        permission: 'read-results'
    },
    {
        title: 'Manage Exams',
        href: '/exams',
        icon: Receipt,
        permission: 'read-exams',
    }
    // {
    //     title: 'Manage Reports',
    //     href: '/reports',
    //     icon: BarChart3,
    // permission: 'read-reports',
    // },
    // {
    //     title: 'Manage Certificates',
    //     href: '/certificates',
    //     icon: Award,
    // permission: 'read-certificates',
    // },
];

const footerNavItems: NavItem[] = [];

const filteredNavItems = mainNavItems.filter((item) => {
    // If no permission specified, show by default
    if (!item.permission) return true;
    return can(item.permission);
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="bg-purple-900 text-white">
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
            <NavMain :items="filteredNavItems" />
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
