<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Shield, UserCog, Users, Download, ScrollText } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

interface Props {
    class?: string;
}

withDefaults(defineProps<Props>(), {
    class: '',
});

const page = usePage<{
    auth: {
        permissions: string[];
    };
}>();

const mainNavItems = computed<NavItem[]>(() => {
    const auth = page.props.auth ?? {};

    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Download Center',
            href: '/exports',
            icon: Download,
        },
        {
            title: 'Activity Logs',
            href: '/activity-logs',
            icon: ScrollText,
        },
    ];

    const canViewStaff =
        auth.can?.viewStaff ??
        auth.permissions?.includes?.('staff.view') ??
        false;

    if (canViewStaff) {
        items.push({
            title: 'Staff',
            href: '/staff',
            icon: Users,
        });
    }

    const canManageUsers =
        auth.can?.manageUsers ??
        auth.permissions?.includes?.('users.manage') ??
        false;

    if (canManageUsers) {
        items.push({
            title: 'Users',
            href: '/users',
            icon: UserCog,
        });
    }

    const canManageRoles =
        auth.can?.manageRoles ??
        auth.permissions?.includes?.('roles.manage') ??
        auth.permissions?.includes?.('users.manage') ??
        false;

    if (canManageRoles) {
        items.push({
            title: 'Roles',
            href: '/roles',
            icon: Shield,
        });
    }

    return items;
    
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="app-sidebar" :class="class">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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
