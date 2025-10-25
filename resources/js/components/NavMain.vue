<script setup lang="ts">
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { ChevronRight } from 'lucide-vue-next';

interface SidebarGroupConfig {
    id: string;
    label?: string | null;
    icon?: unknown;
    items: NavItem[];
}

const props = defineProps<{
    groups: SidebarGroupConfig[];
}>();

const page = usePage();

const availableGroups = computed(() =>
    (props.groups ?? []).filter((group) => group.items && group.items.length > 0),
);

const openGroupId = ref<string | null>(null);

const hasActiveItem = (group: SidebarGroupConfig) =>
    group.items?.some((item) => urlIsActive(item.href, page.url));

const ensureOpenGroup = (forceDefault = false) => {
    // If an active link exists, ensure its group is expanded.
    const activeGroup = availableGroups.value.find((group) => hasActiveItem(group));
    if (activeGroup) {
        openGroupId.value = activeGroup.id;
        return;
    }

    if (openGroupId.value && availableGroups.value.some((group) => group.id === openGroupId.value)) {
        return;
    }

    if (forceDefault && availableGroups.value.length > 0) {
        openGroupId.value = availableGroups.value[0].id;
    }
};

watch(
    () => page.url,
    () => {
        ensureOpenGroup();
    },
);

watch(
    availableGroups,
    () => {
        if (!availableGroups.value.some((group) => group.id === openGroupId.value)) {
            openGroupId.value = null;
        }
        ensureOpenGroup(true);
    },
    { immediate: true },
);

onMounted(() => {
    ensureOpenGroup(true);
});

const toggleGroup = (groupId: string) => {
    openGroupId.value = openGroupId.value === groupId ? null : groupId;
};

const groupTitle = (group: SidebarGroupConfig, index: number) =>
    group.label ?? `Group ${index + 1}`;
</script>

<template>
    <div class="flex flex-col gap-2 px-2 py-0">
        <div
            v-for="(group, index) in availableGroups"
            :key="group.id"
            class="rounded-md"
        >
            <button
                type="button"
                class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm font-medium text-sidebar-foreground transition-colors hover:bg-muted/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                @click="toggleGroup(group.id)"
                :aria-expanded="openGroupId === group.id"
            >
                <div class="flex items-center gap-2 truncate">
                    <component v-if="group.icon" :is="group.icon" class="h-4 w-4 flex-shrink-0" />
                    <span class="truncate">{{ groupTitle(group, index) }}</span>
                </div>
                <ChevronRight
                    class="ml-auto h-4 w-4 flex-shrink-0 transition-transform duration-200"
                    :class="{ 'rotate-90': openGroupId === group.id }"
                />
            </button>

            <transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-96 opacity-100"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="max-h-96 opacity-100"
                leave-to-class="max-h-0 opacity-0"
            >
                <div
                    v-show="openGroupId === group.id"
                    class="mt-1 overflow-hidden rounded-md bg-muted/10 pl-1 pr-1"
                >
                    <SidebarMenu class="space-y-1 px-1 py-1">
                        <SidebarMenuItem v-for="item in group.items" :key="item.title">
                            <SidebarMenuButton
                                as-child
                                :is-active="urlIsActive(item.href, page.url)"
                                :tooltip="item.title"
                                class="w-full justify-start gap-2 px-2 py-1.5 text-sm"
                            >
                                <Link :href="item.href">
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="h-4 w-4 flex-shrink-0"
                                    />
                                    <span class="truncate">{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </div>
            </transition>
        </div>
    </div>
</template>
