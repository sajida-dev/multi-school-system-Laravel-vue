<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Search, AlignJustify, Bell, Monitor, Moon, Sun, Search as SearchIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import NotificationDropdown from '@/components/NotificationDropdown.vue';
import { useAppearance } from '@/composables/useAppearance';
import ThemeSwitch from '@/components/ThemeSwitch.vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''),
);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [

];

const notifications = ref([
    { id: 1, title: 'Welcome!', body: 'Thanks for joining the platform.', time: new Date().toISOString(), link: '/dashboard', read: false },
    { id: 2, title: 'Profile Updated', body: 'Your profile was updated successfully.', time: new Date().toISOString(), link: '/settings/profile', read: false },
]);

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

function markNotificationRead(notificationOrId: any) {
    const id = typeof notificationOrId === 'object' ? notificationOrId.id : notificationOrId;
    const n = notifications.value.find(n => n.id === id);
    if (n) n.read = true;
}

const { appearance, updateAppearance } = useAppearance();
const searchQuery = ref('');

const themeTabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;

function onSearch() {
    // You can emit or handle search here
    // For now, just log
    if (searchQuery.value.trim()) {
        // Implement your search logic or Inertia visit here
        // router.get(route('search'), { q: searchQuery.value })
        // For now:
        console.log('Search:', searchQuery.value);
    }
}
</script>
<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center justify-between px-4 md:max-w-7xl">
                <!-- Search bar and theme switcher -->
                <div class="flex items-center gap-2 flex-1">
                    <form @submit.prevent="onSearch" class="relative w-full max-w-xs hidden md:block">
                        <input v-model="searchQuery" type="text" placeholder="Search..."
                            class="w-full rounded-md border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 py-2 pl-9 pr-3 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary/40 transition" />
                        <SearchIcon class="absolute left-2 top-2.5 h-4 w-4 text-gray-400 dark:text-neutral-500" />
                    </form>
                    <ThemeSwitch compact class="ml-2" />
                </div>
                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer md:hidden">
                            <SearchIcon class="size-5 opacity-80 group-hover:opacity-100" />
                        </Button>
                        <NotificationDropdown :notifications="notifications" @read="markNotificationRead"
                            @mark-as-read="markNotificationRead" />
                        <div class="hidden space-x-1 lg:flex">
                            <template v-for="item in rightNavItems" :key="item.title">
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button variant="ghost" size="icon" as-child
                                                class="group h-9 w-9 cursor-pointer">
                                                <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component :is="item.icon"
                                                        class="size-5 opacity-80 group-hover:opacity-100" />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button variant="ghost" size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary">
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.profile_photo_url" :src="auth.user.profile_photo_url"
                                        class="w-full h-full object-cover" :alt="auth.user.name" />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>
    </div>
</template>
