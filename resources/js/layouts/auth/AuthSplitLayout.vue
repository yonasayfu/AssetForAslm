<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import GlassCard from '@/components/GlassCard.vue';
import type { AppPageProps } from '@/types';
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage<AppPageProps>();
const name = page.props.name;
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center overflow-hidden bg-slate-100 px-8 transition-colors dark:bg-slate-950 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div class="relative hidden h-full flex-col bg-slate-900 p-10 text-white lg:flex">
            <div class="absolute inset-0 bg-slate-900/95" />
            <Link :href="home()" class="relative z-20 flex items-center text-lg font-medium">
                <AppLogoIcon class="mr-2 size-8 fill-current text-white" />
                {{ name }}
            </Link>
            <div v-if="quote" class="relative z-20 mt-auto space-y-2 text-sm">
                <p class="text-base">&ldquo;{{ quote.message }}&rdquo;</p>
                <footer class="text-xs text-slate-300">— {{ quote.author }}</footer>
            </div>
        </div>

        <div class="relative flex items-center justify-center lg:p-12">
            <GlassCard class="w-full max-w-sm" padding="p-0" content-class="">
                <div class="px-6 py-8 sm:px-8 sm:py-10">
                    <div class="space-y-2 text-center">
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
                    <div class="mt-6 space-y-4">
                        <slot />
                    </div>
                </div>
            </GlassCard>
        </div>
    </div>
</template>
