<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassCard from '@/components/GlassCard.vue';
import MetricCard from '@/components/dashboard/MetricCard.vue';
import TrendSparkline from '@/components/dashboard/TrendSparkline.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    AlertTriangle,
    CalendarClock,
    Clock,
    Download as DownloadIcon,
    ShieldCheck,
    Users,
    UserCheck,
} from 'lucide-vue-next';

type Metric = {
    label: string;
    value: number | string;
    description?: string;
    change?: {
        direction: 'up' | 'down' | 'flat';
        percentage: number;
        label?: string;
    } | null;
    icon?: string;
};

const props = defineProps<{
    metrics: Metric[];
    staffTrend: {
        labels: string[];
        series: number[];
    };
    maintenance: Array<{
        id: string;
        title: string;
        location?: string;
        due_on: string;
        priority: 'Low' | 'Medium' | 'High';
        status: string;
    }>;
    recentExports: Array<{
        id: string;
        name: string;
        type: string;
        status: string;
        completed_at: string | null;
        requested_by?: string | null;
    }>;
    recentActivity: Array<{
        id: number | string;
        description: string | null;
        action: string | null;
        causer: string | null;
        occurred_at: string | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const iconRegistry = {
    Users,
    UserCheck,
    ShieldCheck,
    Download: DownloadIcon,
} as const;

const resolvedMetrics = computed(() =>
    (props.metrics ?? []).map((metric) => ({
        ...metric,
        icon: metric.icon ? iconRegistry[metric.icon as keyof typeof iconRegistry] ?? Users : null,
    })),
);

const maintenanceTone = (priority: string) => {
    switch (priority) {
        case 'High':
            return 'text-rose-600 dark:text-rose-400';
        case 'Medium':
            return 'text-amber-600 dark:text-amber-400';
        default:
            return 'text-emerald-600 dark:text-emerald-400';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 px-4 py-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <MetricCard
                    v-for="metric in resolvedMetrics"
                    :key="metric.label"
                    :label="metric.label"
                    :value="metric.value"
                    :description="metric.description"
                    :change="metric.change"
                    :icon="metric.icon ?? undefined"
                />
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <TrendSparkline
                    class="lg:col-span-1"
                    :labels="staffTrend.labels"
                    :series="staffTrend.series"
                />

                <GlassCard
                    variant="lite"
                    padding="p-0"
                    class="lg:col-span-2"
                >
                    <div class="border-b border-slate-200/70 px-5 py-4 dark:border-slate-800/60">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-100">
                            <CalendarClock class="size-5 text-indigo-500" />
                            Upcoming Maintenance &amp; Reviews
                        </div>
                    </div>
                    <div class="divide-y divide-slate-200/70 dark:divide-slate-800/60">
                        <div
                            v-for="item in maintenance"
                            :key="item.id"
                            class="flex flex-col gap-2 px-5 py-4 text-sm text-slate-600 dark:text-slate-300 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-start gap-3">
                                <div class="mt-1 rounded-lg bg-indigo-500/10 p-2 text-indigo-500 dark:bg-indigo-500/20">
                                    <AlertTriangle class="size-4" />
                                </div>
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">
                                        {{ item.title }}
                                    </p>
                                    <p v-if="item.location" class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ item.location }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    <Clock class="size-3.5" />
                                    Due {{ item.due_on }}
                                </span>
                                <span class="rounded-full px-2 py-1 font-medium" :class="maintenanceTone(item.priority)">
                                    {{ item.priority }} priority
                                </span>
                                <span class="rounded-full bg-indigo-500/10 px-2 py-1 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                        <div v-if="!maintenance.length" class="px-5 py-6 text-sm text-slate-500 dark:text-slate-400">
                            No upcoming work scheduled.
                        </div>
                    </div>
                </GlassCard>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <GlassCard variant="lite" padding="p-0" class="lg:col-span-2">
                    <div class="border-b border-slate-200/70 px-5 py-4 text-sm font-semibold text-slate-800 dark:border-slate-800/60 dark:text-slate-100">
                        Recent Data Exports
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200/70 text-sm dark:divide-slate-800/60">
                            <thead class="bg-slate-50/70 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">Export</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3">Completed</th>
                                    <th class="px-5 py-3">Requested by</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 bg-white/80 dark:divide-slate-800/60 dark:bg-slate-900/60">
                                <tr v-if="!recentExports.length">
                                    <td colspan="4" class="px-5 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                        No exports have been processed yet.
                                    </td>
                                </tr>
                                <tr v-for="exportItem in recentExports" :key="exportItem.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-slate-800 dark:text-slate-100">
                                            {{ exportItem.name }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ exportItem.type }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ exportItem.status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ exportItem.completed_at ?? 'In progress' }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ exportItem.requested_by ?? 'System' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </GlassCard>

                <GlassCard variant="lite" padding="p-0">
                    <div class="border-b border-slate-200/70 px-5 py-4 text-sm font-semibold text-slate-800 dark:border-slate-800/60 dark:text-slate-100">
                        Recent Activity
                    </div>
                    <div class="divide-y divide-slate-200/70 dark:divide-slate-800/60">
                        <div v-if="!recentActivity.length" class="px-5 py-6 text-sm text-slate-500 dark:text-slate-400">
                            No activity logged yet.
                        </div>
                        <div
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="flex gap-3 px-5 py-4 text-sm text-slate-600 dark:text-slate-300"
                        >
                            <div class="rounded-full bg-indigo-500/10 p-2 text-indigo-500 dark:bg-indigo-500/20">
                                <Clock class="size-4" />
                            </div>
                            <div class="space-y-1">
                                <p class="font-medium text-slate-800 dark:text-slate-100">
                                    {{ activity.description ?? activity.action ?? 'Activity' }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ activity.causer ?? 'System' }} - {{ activity.occurred_at ?? 'Unknown time' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </GlassCard>
            </div>
        </div>
    </AppLayout>
</template>

