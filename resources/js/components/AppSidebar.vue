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
import {
    BookOpen,
    Download,
    Folder,
    LayoutGrid,
    ScrollText,
    Shield,
    UserCog,
    Users,
    ClipboardList,
    Globe2,
    MessageCircle,
    Settings,
} from 'lucide-vue-next';
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
    navigation?: {
        sidebar?: Array<{
            label?: string | null;
            items: Array<NavItem & { icon?: string }>;
        }>;
    };
}>();

const iconMap: Record<string, unknown> = {
    LayoutGrid,
    Download,
    ScrollText,
    Users,
    UserCog,
    Shield,
    Folder,
    BookOpen,
    ClipboardList,
    Globe2,
    MessageCircle,
    Settings,
};

const hasPermission = (
    permission: string | null | undefined,
    permissions: string[] = [],
) => {
    if (!permission) {
        return true;
    }

    return permissions.includes(permission);
};

const sidebarGroups = computed(() => {
    const permissions = page.props.auth?.permissions ?? [];
    const groups = page.props.navigation?.sidebar ?? [];

    return groups
        .map((group, index) => {
            const items = (group.items ?? [])
                .filter((item) =>
                    hasPermission(item.permission ?? null, permissions),
                )
                .map((item) => ({
                    ...item,
                    href: item.href === '/dashboard' ? dashboard() : item.href,
                    icon:
                        typeof item.icon === 'string' && iconMap[item.icon]
                            ? iconMap[item.icon]
                            : item.icon,
                }));

            if (!items.length) {
                return null;
            }

            return {
                id: group.label ?? `group-${index}`,
                label: group.label ?? null,
                icon:
                    typeof (group as any).icon === 'string' &&
                    iconMap[(group as any).icon]
                        ? iconMap[(group as any).icon as string]
                        : (group as any).icon,
                items,
            };
        })
        .filter(Boolean);
});

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="app-sidebar"
        :class="class"
    >
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
            <NavMain :groups="sidebarGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
