++ resources/js/components/dashboard/MetricCard.vue
<script setup lang="ts">
import { ArrowDownRight, ArrowUpRight, Minus } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

type TrendDirection = 'up' | 'down' | 'flat';

const props = defineProps<{
    label: string;
    value: number | string;
    description?: string;
    icon?: Component | string;
    change?: {
        direction: TrendDirection;
        percentage: number;
        label?: string;
    } | null;
}>();

const iconComponent = computed<Component | null>(() => {
    if (!props.icon) {
        return null;
    }

    return props.icon as Component;
});

const changeIcon = computed(() => {
    if (!props.change) {
        return null;
    }

    if (props.change.direction === 'up') {
        return ArrowUpRight;
    }

    if (props.change.direction === 'down') {
        return ArrowDownRight;
    }

    return Minus;
});

const changeTone = computed(() => {
    if (!props.change) {
        return 'text-slate-500';
    }

    switch (props.change.direction) {
        case 'up':
            return 'text-emerald-600 dark:text-emerald-400';
        case 'down':
            return 'text-rose-600 dark:text-rose-400';
        default:
            return 'text-slate-500 dark:text-slate-400';
    }
});
</script>

<template>
    <div
        class="relative overflow-hidden rounded-xl border border-slate-200/70 bg-white/80 p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-slate-800/60 dark:bg-slate-900/60"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ label }}
                </p>
                <p class="text-3xl font-semibold text-slate-900 dark:text-slate-50">
                    {{ value }}
                </p>
            </div>

            <div
                v-if="iconComponent"
                class="flex size-10 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20"
            >
                <component :is="iconComponent" class="size-5" />
            </div>
        </div>

        <div v-if="description || change" class="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <div v-if="description" class="max-w-[70%]">
                {{ description }}
            </div>

            <div v-if="change" class="flex items-center gap-1 font-medium" :class="changeTone">
                <component :is="changeIcon" class="size-3.5" />
                <span>{{ change.percentage }}%</span>
                <span v-if="change.label" class="ml-1 text-xs font-normal text-slate-400 dark:text-slate-500">
                    {{ change.label }}
                </span>
            </div>
        </div>
    </div>
</template>
