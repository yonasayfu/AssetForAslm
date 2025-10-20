<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import Pagination from '@/components/Pagination.vue';
import { confirmDialog } from '@/lib/confirm';
import { useTableFilters } from '@/composables/useTableFilters';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface UserSummary {
    id: number;
    name: string;
    email: string;
}

interface StaffSummary {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
    phone: string | null;
    job_title: string | null;
    status: 'active' | 'inactive';
    user: UserSummary | null;
}

interface StatCard {
    label: string;
    value: number;
    tone?: 'primary' | 'success' | 'muted';
}

interface StatusOption {
    label: string;
    value: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    from?: number | null;
}

const props = defineProps<{
    staff: {
        data: StaffSummary[];
        links: PaginationLink[];
        meta?: PaginationMeta;
    };
    stats?: StatCard[];
    filters?: {
        search?: string;
        status?: string | null;
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number;
    };
    statuses?: StatusOption[];
    can: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}>();

const status = ref<string | null>(props.filters?.status ?? null);

const tableFilters = useTableFilters({
    route: '/staff',
    initial: {
        search: props.filters?.search ?? '',
        sort: props.filters?.sort ?? '',
        direction: props.filters?.direction ?? 'asc',
        per_page: props.filters?.per_page ?? 10,
    },
    extra: () => ({
        status: status.value || undefined,
    }),
});

const { search, sort, direction, perPage, apply, toggleSort } = tableFilters;

watch(status, () => apply());

const statusOptions = computed<StatusOption[]>(
    () =>
        props.statuses ?? [
            { label: 'All', value: null },
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' },
        ],
);

const stats = computed<StatCard[]>(() => props.stats ?? []);
const staffMembers = computed<StaffSummary[]>(() => props.staff?.data ?? []);
const hasResults = computed<boolean>(() => staffMembers.value.length > 0);
const paginationLinks = computed(() => props.staff?.links ?? []);
const paginationFrom = computed(() => props.staff?.meta?.from ?? 1);

const destroy = async (staffMember: StaffSummary) => {
    const accepted = await confirmDialog({
        title: 'Remove staff member?',
        message: `This will archive ${staffMember.full_name}.`,
        confirmText: 'Remove',
        cancelText: 'Cancel',
    });

    if (!accepted) {
        return;
    }

    router.delete(`/staff/${staffMember.id}`, {
        preserveScroll: true,
    });
};

const selectStatus = (value: string | null) => {
    status.value = value;
};

const statusClasses = (value: string | null) => [
    'inline-flex items-center rounded-full px-3 py-1 text-sm font-medium transition',
    status.value === value
        ? 'bg-indigo-600 text-white shadow'
        : 'bg-white/80 text-slate-600 hover:bg-white dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-900',
];

const statTone = (tone?: string) => {
    switch (tone) {
        case 'success':
            return 'text-emerald-600 dark:text-emerald-300';
        case 'muted':
            return 'text-slate-500 dark:text-slate-300';
        default:
            return 'text-indigo-600 dark:text-indigo-300';
    }
};
</script>

<template>
    <Head title="Staff" />

    <AppLayout :breadcrumbs="[{ title: 'Staff', href: '/staff' }]">
    <div class="space-y-6">
        <div class="liquidGlass-wrapper">
            <span class="liquidGlass-inner-shine" aria-hidden="true" />
            <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        Team directory
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Manage staff records and link user accounts.
                    </p>
                </div>

                <div v-if="can.create" class="flex items-center gap-2">
                    <GlassButton as="span">
                        <Link href="/staff/create" class="flex items-center gap-2">
                            <span>New staff</span>
                        </Link>
                    </GlassButton>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <GlassCard
                v-for="metric in stats"
                :key="metric.label"
                variant="lite"
                padding="px-5 py-6"
                content-class="space-y-1"
            >
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ metric.label }}
                </p>
                <p class="text-3xl font-semibold" :class="statTone(metric.tone)">
                    {{ metric.value }}
                </p>
            </GlassCard>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="search-glass relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <Search class="size-4" />
                </span>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search team (name, email, role)"
                    class="w-full rounded-lg border border-transparent bg-white/80 py-2 pl-10 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-300 focus:ring-0 dark:bg-slate-900/70 dark:text-slate-200"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <label class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Status
                </label>
                <button
                    v-for="option in statusOptions"
                    :key="option.label"
                    type="button"
                    :class="statusClasses(option.value)"
                    @click="selectStatus(option.value)"
                >
                    {{ option.label }}
                </button>
            </div>

            <div class="flex items-center gap-2">
                <label for="perPage" class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Per Page</label>
                <select
                    id="perPage"
                    v-model.number="perPage"
                    class="rounded-lg border border-transparent bg-white/80 px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-0 dark:bg-slate-900/70 dark:text-slate-200"
                >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/70 bg-white/70 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/50">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50/80 dark:bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            #
                        </th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 cursor-pointer select-none"
                            @click="toggleSort('last_name')"
                        >
                            Name
                            <span v-if="sort === 'last_name'" class="ml-1 text-[10px] text-slate-400">
                                {{ direction === 'asc' ? '?' : '?' }}
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Contact
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Role
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Status
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/90 dark:divide-slate-800 dark:bg-slate-950/40">
                    <tr v-if="!hasResults">
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-300">
                            No staff members match your filters yet. Add a new profile to get started.
                        </td>
                    </tr>
                    <tr v-for="(member, index) in staffMembers" v-else :key="member.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/50">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            {{ paginationFrom + index }}
                        </td>
                        <td class="px-5 py-4 text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ member.full_name }}
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <div class="font-medium text-slate-700 dark:text-slate-200">
                                {{ member.email ?? '-' }}
                            </div>
                            <div v-if="member.phone" class="text-xs text-slate-500 dark:text-slate-400">
                                {{ member.phone }}
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <div>{{ member.job_title ?? '-' }}</div>
                            <div v-if="member.user" class="text-xs text-slate-500 dark:text-slate-400">
                                Linked user: {{ member.user.name }}
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold',
                                    member.status === 'active'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
                                        : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                ]"
                            >
                                {{ member.status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right text-sm">
                            <div class="flex justify-end gap-2">
                                <GlassButton v-if="can.edit" size="sm" as="span">
                                    <Link :href="`/staff/${member.id}/edit`">
                                        Edit
                                    </Link>
                                </GlassButton>
                                <GlassButton
                                    v-if="can.delete"
                                    size="sm"
                                    class="bg-red-500/10 text-red-600 hover:bg-red-500/20 dark:bg-red-500/20 dark:text-red-200"
                                    @click="destroy(member)"
                                >
                                    Remove
                                </GlassButton>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end">
            <Pagination :links="paginationLinks" />
        </div>
    </div>
    </AppLayout>
</template>




