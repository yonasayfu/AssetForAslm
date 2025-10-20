<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import GlassCard from '@/components/GlassCard.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-gradient-to-b from-slate-50 via-white to-slate-100 p-6 transition-colors dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col items-center gap-6">
                <Link
                    :href="home()"
                    class="flex items-center gap-2 font-medium text-slate-700 transition-colors hover:text-slate-900 dark:text-slate-200 dark:hover:text-white"
                >
                    <span class="flex h-10 w-10 items-center justify-center">
                        <AppLogoIcon class="size-9 fill-current" />
                    </span>
                </Link>

                <GlassCard
                    class="w-full"
                    padding="px-6 py-6 sm:px-8 sm:py-8"
                    content-class="space-y-6 text-center"
                >
                    <div class="space-y-2">
                        <h1 v-if="title" class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="text-sm text-slate-600 dark:text-slate-300"
                        >
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </GlassCard>
            </div>
        </div>
    </div>
</template>
