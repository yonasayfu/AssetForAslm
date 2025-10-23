<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import GlassCard from '@/components/GlassCard.vue';
import GlobalSearch from '@/components/GlobalSearch.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const showBreadcrumbs = computed(
    () => props.breadcrumbs && props.breadcrumbs.length > 0,
);

</script>

<template>
    <div class="sticky top-0 z-30">
        <GlassCard
            as="header"
            variant="lite"
            padding="px-4 py-3 sm:px-6"
            content-class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            class="shadow-none overflow-visible"
        >
            <div class="flex items-center gap-3">
                <SidebarTrigger class="btn-glass btn-glass-sm -ml-1" />
                <Breadcrumbs v-if="showBreadcrumbs" :breadcrumbs="breadcrumbs" />
            </div>

            <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                <div class="w-full max-w-sm sm:w-auto">
                    <GlobalSearch />
                </div>
                <slot name="actions" />
            </div>
        </GlassCard>
    </div>
</template>
