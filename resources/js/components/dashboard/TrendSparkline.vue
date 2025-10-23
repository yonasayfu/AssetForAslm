++ resources/js/components/dashboard/TrendSparkline.vue
<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        labels: string[];
        series: number[];
        stroke?: string;
        fill?: string;
    }>(),
    {
        stroke: '#6366f1',
        fill: 'rgba(99, 102, 241, 0.15)',
    },
);

const points = computed(() => {
    if (!props.series.length) {
        return '';
    }

    const max = Math.max(...props.series, 1);
    const lastIndex = props.series.length - 1;

    return props.series
        .map((value, index) => {
            const x = lastIndex === 0 ? 0 : (index / lastIndex) * 100;
            const y = 100 - (value / max) * 100;

            return `${x},${y}`;
        })
        .join(' ');
});

const areaPoints = computed(() => {
    if (!points.value) {
        return '';
    }

    return `0,100 ${points.value} 100,100`;
});

const latestValue = computed(() => props.series.at(-1) ?? 0);
</script>

<template>
    <div class="rounded-xl border border-slate-200/70 bg-white/80 p-5 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/60">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Team Growth
                </p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-50">
                    {{ latestValue }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    New staff added last month
                </p>
            </div>
            <div class="h-16 w-24">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full">
                    <polygon
                        :points="areaPoints"
                        :fill="fill"
                    />
                    <polyline
                        :points="points"
                        :fill="'none'"
                        :stroke="stroke"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-2 text-xs text-slate-500 dark:text-slate-400 sm:grid-cols-6">
            <div v-for="(label, index) in labels" :key="label" class="text-center">
                <span>{{ label }}</span>
            </div>
        </div>
    </div>
</template>
