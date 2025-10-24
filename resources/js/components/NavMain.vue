<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface SidebarGroupConfig {
    label?: string | null;
    items: NavItem[];
}

const props = defineProps<{
    groups: SidebarGroupConfig[];
}>();

const page = usePage();

const groups = computed(() =>
    (props.groups ?? []).filter((group) => group.items && group.items.length > 0),
);
</script>

<template>
    <div class="flex flex-col gap-4 px-2 py-0">
        <SidebarGroup v-for="group in groups" :key="group.label ?? 'default'" class="py-0">
            <SidebarGroupLabel v-if="group.label">{{ group.label }}</SidebarGroupLabel>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in group.items" :key="item.title">
                    <SidebarMenuButton
                        as-child
                        :is-active="urlIsActive(item.href, page.url)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component v-if="item.icon" :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </div>
</template>
