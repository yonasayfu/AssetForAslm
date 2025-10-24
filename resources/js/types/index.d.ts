import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User | null;
    roles: string[];
    permissions: string[];
    can?: {
        viewStaff?: boolean;
        manageUsers?: boolean;
        manageRoles?: boolean;
        [key: string]: boolean | undefined;
    };
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | string;
    isActive?: boolean;
    description?: string;
    badge?: string;
    permission?: string | null;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    breadcrumbs?: BreadcrumbItem[];
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    account_status: string;
    account_type: string;
    approved_at: string | null;
    approved_by: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface ActivityLogChange {
    old?: unknown;
    new?: unknown;
}

export interface ActivityLog {
    id: number;
    causer_id: number | null;
    causer: {
        id: number;
        name: string;
        email?: string | null;
    } | null;
    action: string;
    description: string | null;
    subject_type: string;
    subject_id: number | string | null;
    changes: Record<string, ActivityLogChange | null> | null;
    created_at: string;
    updated_at: string;
}
