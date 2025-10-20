<script setup lang="ts">
type TimelineEntry = {
    id: number | string;
    action: string;
    description?: string | null;
    causer?: {
        id: number | string | null;
        name?: string | null;
    } | null;
    changes?: {
        before?: Record<string, unknown> | null;
        after?: Record<string, unknown> | null;
    } | null;
    created_at?: string | null;
    created_at_for_humans?: string | null;
};

const props = defineProps<{
    entries: TimelineEntry[];
}>();

const normaliseLabel = (label: string) =>
    label
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());

const displayValue = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (Array.isArray(value)) {
        return value.length ? value.join(', ') : '—';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
};
</script>

<template>
    <div>
        <div v-if="!entries.length" class="rounded-lg border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-300">
            No recent activity yet.
        </div>

        <ul
            v-else
            class="relative border-l border-slate-200 pl-6 dark:border-slate-700"
        >
            <li
                v-for="entry in entries"
                :key="entry.id"
                class="relative mb-6 last:mb-0"
            >
                <span
                    class="absolute -left-[9px] inline-flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-indigo-500 dark:border-slate-900"
                    aria-hidden="true"
                />
                <div class="flex flex-col gap-1">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            {{ entry.created_at_for_humans ?? entry.created_at }}
                        </span>
                        <span v-if="entry.causer?.name" class="text-xs text-slate-500 dark:text-slate-400">
                            by {{ entry.causer.name }}
                        </span>
                    </div>

                    <div class="font-medium text-slate-900 dark:text-slate-100">
                        {{ entry.description ?? normaliseLabel(entry.action) }}
                    </div>

                    <div
                        v-if="entry.changes && (entry.changes.before || entry.changes.after)"
                        class="mt-2 space-y-3 text-xs text-slate-600 dark:text-slate-300"
                    >
                        <template v-if="entry.changes.before && entry.changes.after">
                            <div
                                v-for="(afterValue, key) in entry.changes.after"
                                :key="key"
                                class="grid gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700"
                            >
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    {{ normaliseLabel(key) }}
                                </div>
                                <div class="grid gap-2 md:grid-cols-2">
                                    <div class="rounded-md bg-slate-100/70 p-2 dark:bg-slate-800/50">
                                        <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                            Before
                                        </span>
                                        <span class="text-slate-700 dark:text-slate-200">
                                            {{ displayValue(entry.changes.before?.[key]) }}
                                        </span>
                                    </div>
                                    <div class="rounded-md bg-emerald-50/80 p-2 dark:bg-emerald-500/20">
                                        <span class="block text-[10px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-200">
                                            After
                                        </span>
                                        <span class="text-slate-700 dark:text-slate-200">
                                            {{ displayValue(afterValue) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-else-if="entry.changes.after">
                            <div
                                v-for="(afterValue, key) in entry.changes.after"
                                :key="key"
                                class="rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700"
                            >
                                <span class="font-semibold text-slate-700 dark:text-slate-200">
                                    {{ normaliseLabel(key) }}:
                                </span>
                                <span class="ml-1 text-slate-600 dark:text-slate-300">
                                    {{ displayValue(afterValue) }}
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>
