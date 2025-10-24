<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import type { BreadcrumbItemType } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<{
    auth: { user: { name: string; email: string } | null };
    impersonation?: {
        active: boolean;
        impersonator: { name: string; email: string } | null;
        target: { name: string; email: string } | null;
    };
}>();

const user = computed(() => page.props.auth.user);
const impersonation = computed(() => page.props.impersonation ?? { active: false, impersonator: null, target: null });
const isImpersonating = computed(() => impersonation.value?.active ?? false);
const impersonatedTargetName = computed(() => impersonation.value?.target?.name ?? user.value?.name ?? 'this user');
const impersonatorName = computed(() => impersonation.value?.impersonator?.name ?? 'your account');
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar class="print:hidden" />
        <AppContent variant="sidebar" class="overflow-x-hidden print:bg-white print:p-0">
            <div v-if="isImpersonating" class="bg-yellow-500 text-white text-center p-2 text-sm">
                You are browsing as {{ impersonatedTargetName }}. Return to {{ impersonatorName }}?
                <Link href="/impersonate/leave" class="ml-1 underline">Leave impersonation</Link>
            </div>
            <div
                class="relative min-h-screen w-full bg-gradient-to-br from-slate-50 via-slate-100 to-white px-4 py-6 transition-colors dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 sm:px-6 lg:px-10 print:bg-white print:p-0"
            >
                <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 print:max-w-full print:px-6 print:py-6">
                    <AppSidebarHeader :breadcrumbs="breadcrumbs" class="print:hidden">
                        <template #actions>
                            <div class="flex items-center gap-2">
                                <ThemeToggle />
                                <NotificationBell />
                            </div>
                        </template>
                    </AppSidebarHeader>
                    <main class="flex flex-1 flex-col gap-6 pb-10 print:gap-4 print:p-0">
                        <slot />
                    </main>
                </div>

                <div
                    class="pointer-events-none absolute -top-10 left-20 hidden h-64 w-64 rounded-full bg-blue-400/20 blur-3xl dark:bg-blue-500/30 md:block print:hidden"
                />
                <div
                    class="pointer-events-none absolute bottom-16 right-12 hidden h-72 w-72 rounded-full bg-indigo-400/20 blur-3xl dark:bg-indigo-500/25 md:block print:hidden"
                />
            </div>
        </AppContent>
    </AppShell>
</template>
