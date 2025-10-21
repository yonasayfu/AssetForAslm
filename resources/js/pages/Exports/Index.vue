<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import Pagination from '@/components/Pagination.vue';
import ResourceToolbar from '@/components/ResourceToolbar.vue';
import { useTableFilters } from '@/composables/useTableFilters';
import { Head, router } from '@inertiajs/vue3';
import { Download, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface ExportResource {
    uuid: string;
    name: string;
    type: string;
    format: string;
    status: string;
    record_count: number;
    completed_at: string | null;
    completed_at_human: string | null;
    download_url: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    exports: {
        data: ExportResource[];
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        per_page?: number;
    };
}>();

const tableFilters = useTableFilters({
    route: '/exports',
    initial: {
        search: props.filters?.search ?? '',
        per_page: props.filters?.per_page ?? 5,
    },
});

const { search, perPage } = tableFilters;

const exportsList = computed(() => props.exports?.data ?? []);
const paginationLinks = computed(() => props.exports?.links ?? []);
const hasResults = computed(() => exportsList.value.length > 0);

const destroy = (item: ExportResource) => {
    router.delete(`/exports/${item.uuid}`, {
        preserveScroll: true,
    });
};

const statusBadge = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-emerald-500/20 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-100';
        case 'failed':
            return 'bg-red-500/20 text-red-600 dark:bg-red-500/20 dark:text-red-200';
        default:
            return 'bg-slate-200/70 text-slate-600 dark:bg-slate-800/60 dark:text-slate-300';
    }
};
</script>

<template>
    <Head title="Download Center" />

    <AppLayout :breadcrumbs="[{ title: 'Download Center', href: '/exports' }]">
    <div class="space-y-6">
        <ResourceToolbar
            title="Download center"
            description="Access the exports you have generated. Files stay available for 90 days."
            :show-create="false"
            :show-export="false"
            :show-print="false"
        />

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-4.35-4.35M18 10.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                    </svg>
                </span>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search exports"
                    class="w-full rounded-lg border border-transparent bg-white/80 py-2 pl-10 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-300 focus:ring-0 dark:bg-slate-900/70 dark:text-slate-200"
                />
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400" for="perPage">Per Page</label>
                <select
                    id="perPage"
                    v-model.number="perPage"
                    class="rounded-lg border border-transparent bg-white/80 px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-0 dark:bg-slate-900/70 dark:text-slate-200"
                >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
            </div>
        </div>

        <GlassCard variant="lite" padding="p-0">
            <div class="overflow-hidden rounded-xl border border-slate-200/70 bg-white/80 dark:border-slate-800/60 dark:bg-slate-900/50">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Format</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Records</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Completed</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/90 dark:divide-slate-800 dark:bg-slate-950/40">
                        <tr v-if="!hasResults">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-300">
                                No exports yet. Generate an export from any module to see it appear here.
                            </td>
                        </tr>
                        <tr v-for="item in exportsList" :key="item.uuid" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/50">
                            <td class="px-5 py-4 text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ item.name }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                {{ item.type }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                {{ item.format }}
                            </td>
                            <td class="px-5 py-4 text-center text-sm text-slate-600 dark:text-slate-300">
                                {{ item.record_count }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                <div>{{ item.completed_at_human ?? 'Pending' }}</div>
                                <div v-if="item.completed_at" class="text-xs text-slate-400">
                                    {{ item.completed_at }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="statusBadge(item.status)"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <GlassButton
                                        size="sm"
                                        as="a"
                                        :href="item.download_url"
                                        class="flex items-center gap-1"
                                    >
                                        <Download class="size-4" />
                                        Download
                                    </GlassButton>
                                    <GlassButton
                                        size="sm"
                                        type="button"
                                        class="flex items-center gap-1 bg-red-500/10 text-red-600 hover:bg-red-500/20 dark:bg-red-500/20 dark:text-red-200"
                                        @click="destroy(item)"
                                    >
                                        <Trash2 class="size-4" />
                                        Delete
                                    </GlassButton>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </GlassCard>

        <div class="flex items-center justify-end">
            <Pagination :links="paginationLinks" />
        </div>
    </div>
    </AppLayout>
</template>
