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
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-slate-100 p-6 transition-colors dark:bg-slate-950 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium text-slate-700 transition-colors hover:text-slate-900 dark:text-slate-200 dark:hover:text-white"
            >
                <AppLogoIcon class="size-9 fill-current" />
            </Link>

            <GlassCard padding="p-0" content-class="">
                <div class="px-8 py-8 text-center">
                    <h1 v-if="title" class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-2 text-sm text-slate-600 dark:text-slate-300"
                    >
                        {{ description }}
                    </p>
                    <div class="mt-6 space-y-4 text-left">
                        <slot />
                    </div>
                </div>
            </GlassCard>
        </div>
    </div>
</template>
