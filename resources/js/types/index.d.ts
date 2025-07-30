import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    can?: Record<string, boolean>; // Add this line for permissions
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    phone_number: string;
    profile_photo_path?: string;
    profile_photo_url?: string;
    initials?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles?: Array<{ id: number; name: string; school_id?: number }>; // Added roles property
}

export type BreadcrumbItemType = BreadcrumbItem;
