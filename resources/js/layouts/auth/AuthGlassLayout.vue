<script setup lang="ts">
import GlassCard from '@/components/GlassCard.vue';
import type { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
    }>(),
    {
        title: 'Welcome back',
        description: 'Sign in to continue',
    },
);

const page = usePage<AppPageProps>();
const appName = computed(() => page.props.name ?? 'Asset Workspace');
const year = computed(() => new Date().getFullYear());
</script>

<template>
    <div
        class="min-h-screen grid bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 md:grid-cols-2"
    >
        <div class="relative hidden overflow-hidden md:block">
            <div class="absolute inset-0">
                <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl" />
                <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-indigo-400/20 blur-3xl" />
            </div>
            <div class="relative flex h-full flex-col justify-between p-10 text-slate-700 dark:text-slate-300">
                <div class="text-sm font-medium text-slate-500 dark:text-slate-400">
                    {{ appName }}
                </div>
                <div class="mt-auto space-y-3">
                    <h2 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">
                        Control every asset lifecycle with clarity
                    </h2>
                    <p class="max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Monitor purchases, maintenance, reservations, and turnover from a secure command center.
                    </p>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    &copy; {{ year }} {{ appName }}
                </div>
            </div>
        </div>

        <div class="relative flex items-center justify-center p-6 md:p-10">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-10 right-10 h-40 w-40 rounded-full bg-white/20 dark:bg-white/5 blur-2xl" />
            </div>

            <GlassCard class="w-full max-w-md" padding="p-0" content-class="">
                <div class="px-6 py-6 md:px-8 md:py-8">
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ props.title }}
                    </h1>
                    <p v-if="props.description" class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {{ props.description }}
                    </p>
                    <div class="mt-6">
                        <slot />
                    </div>
                </div>
            </GlassCard>
        </div>
    </div>
</template>
