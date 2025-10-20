<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <div
                class="relative min-h-screen w-full bg-gradient-to-br from-slate-50 via-slate-100 to-white px-4 py-6 transition-colors dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 sm:px-6 lg:px-10"
            >
                <div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
                    <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                    <main class="flex flex-1 flex-col gap-6 pb-10">
                        <slot />
                    </main>
                </div>

                <div
                    class="pointer-events-none absolute -top-10 left-20 hidden h-64 w-64 rounded-full bg-blue-400/20 blur-3xl dark:bg-blue-500/30 md:block"
                />
                <div
                    class="pointer-events-none absolute bottom-16 right-12 hidden h-72 w-72 rounded-full bg-indigo-400/20 blur-3xl dark:bg-indigo-500/25 md:block"
                />
            </div>
        </AppContent>
    </AppShell>
</template>
