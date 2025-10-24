<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassCard from '@/components/GlassCard.vue';
import Pagination from '@/components/Pagination.vue';
import ResourceToolbar from '@/components/ResourceToolbar.vue';
import { confirmDialog } from '@/lib/confirm';
import { useTableFilters } from '@/composables/useTableFilters';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit3, Eye, Search, Trash2, UserCog } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted } from 'vue';

interface UserSummary {
    id: number;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
    has_two_factor: boolean;
    staff: {
        id: number;
        full_name: string;
        status: string;
    } | null;
}

interface StatCard {
    label: string;
    value: number;
    tone?: 'primary' | 'success' | 'muted';
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
    users: {
        data: UserSummary[];
        links: PaginationLink[];
        meta?: PaginationMeta;
    };
    stats: StatCard[];
    filters: {
        search?: string;
        per_page?: number;
    };
    can: {
        create: boolean;
        edit: boolean;
        delete: boolean;
        impersonate: boolean;
    };
    print?: boolean;
}>();

const tableFilters = useTableFilters({
    route: '/users',
    initial: {
        search: props.filters?.search ?? '',
        per_page: props.filters?.per_page ?? 5,
    },
});

const { search, perPage } = tableFilters;

const printMode = computed(() => props.print ?? false);
let printTimer: number | undefined;

const printDocumentTitle = 'Asset Management - User Directory';
const printTimestamp = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date());

const triggerPrint = () => {
    const originalTitle = document.title;
    document.title = printDocumentTitle;
    window.print();
    document.title = originalTitle;
};

const closeAfterPrint = () => {
    if (printMode.value && window.opener && !window.opener.closed) {
        window.close();
    }
};

onMounted(() => {
    if (printMode.value) {
        printTimer = window.setTimeout(() => {
            triggerPrint();
        }, 150);
        window.addEventListener('afterprint', closeAfterPrint);
    }
});

onBeforeUnmount(() => {
    if (printTimer) {
        window.clearTimeout(printTimer);
    }

    window.removeEventListener('afterprint', closeAfterPrint);
});

const buildQueryString = (extra: Record<string, unknown> = {}) => {
    const params = new URLSearchParams();

    if (search.value) {
        params.set('search', search.value);
    }

    params.set('per_page', String(perPage.value));

    Object.entries(extra).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') {
            return;
        }

        params.set(key, String(value));
    });

    const query = params.toString();

    return query ? `?${query}` : '';
};

const users = computed<UserSummary[]>(() => props.users?.data ?? []);
const stats = computed<StatCard[]>(() => props.stats ?? []);
const hasResults = computed<boolean>(() => users.value.length > 0);
const paginationLinks = computed(() => props.users?.links ?? []);

const exportCsv = () => {
    const query = buildQueryString();
    window.open(`/users/export${query}`, '_blank', 'noopener=yes');
};

const printCurrent = () => {
    triggerPrint();
};

const destroy = async (user: UserSummary) => {
    const accepted = await confirmDialog({
        title: 'Delete user?',
        message: `This will remove ${user.name}'s account.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
    });

    if (!accepted) {
        return;
    }

    router.delete(`/users/${user.id}`);
};

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
    <Head title="Users" />

    <AppLayout :breadcrumbs="[{ title: 'Users', href: '/users' }]">
    <div class="space-y-6">
        <ResourceToolbar
            title="User management"
            description="Manage access controls, roles, and permissions for your team."
            :create-route="can.create ? '/users/create' : undefined"
            :show-create="can.create"
            @export="exportCsv"
            @print="printCurrent"
        />

        <div class="hidden print:block text-center text-slate-800">
            <img src="/images/asset-logo.svg" alt="Asset Management" class="mx-auto mb-3 h-12 w-auto print-logo" />
            <h1 class="text-xl font-semibold">Asset Management</h1>
            <p class="text-sm">User Directory</p>
            <p class="text-xs text-slate-500">Printed {{ printTimestamp }}</p>
            <hr class="print-divider" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 print:hidden">
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

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between print:hidden">
            <div class="search-glass relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <Search class="size-4" />
                </span>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search users"
                    class="w-full rounded-lg border border-transparent bg-white/80 py-2 pl-10 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-300 focus:ring-0 dark:bg-slate-900/70 dark:text-slate-200"
                />
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
                    <option :value="100">100</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/70 bg-white/70 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/50 print:border print:bg-white print:shadow-none">
            <table class="min-w-full divide-y divide-slate-200 print-table dark:divide-slate-800">
                <thead class="bg-slate-50/80 dark:bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            User
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Roles
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Direct permissions
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Staff
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 print:hidden">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/90 print:bg-white dark:divide-slate-800 dark:bg-slate-950/40">
                    <tr v-if="!hasResults">
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-300">
                            No users found. Create your first account to get started.
                        </td>
                    </tr>
                    <tr v-for="user in users" v-else :key="user.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/50">
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                <Link
                                    :href="`/users/${user.id}`"
                                    class="transition hover:text-indigo-600 dark:hover:text-indigo-300"
                                >
                                    {{ user.name }}
                                </Link>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ user.email }}
                            </div>
                            <div v-if="user.has_two_factor" class="mt-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                                2FA enabled
                            </div>
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <div v-if="user.roles.length" class="flex flex-wrap gap-2">
                                <span
                                    v-for="role in user.roles"
                                    :key="`${user.id}-${role}`"
                                    class="inline-flex items-center rounded-full bg-slate-200/70 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-800/60 dark:text-slate-200"
                                >
                                    {{ role }}
                                </span>
                            </div>
                            <span v-else class="text-xs text-slate-400 dark:text-slate-500">No roles</span>
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <div v-if="user.permissions.length" class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in user.permissions"
                                    :key="`${user.id}-${permission}`"
                                    class="inline-flex items-center rounded-full bg-emerald-100/80 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                            <span v-else class="text-xs text-slate-400 dark:text-slate-500">Inherited from roles</span>
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <div v-if="user.staff">
                                <span class="font-medium text-slate-700 dark:text-slate-200">
                                    {{ user.staff.full_name }}
                                </span>
                                <span
                                    class="ml-2 rounded-full bg-slate-200/70 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800/60 dark:text-slate-300"
                                >
                                    {{ user.staff.status }}
                                </span>
                            </div>
                            <span v-else class="text-xs text-slate-400 dark:text-slate-500">
                                Not linked
                            </span>
                        </td>

                        <td class="px-5 py-4 text-right text-sm print:hidden">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="`/users/${user.id}`"
                                    class="inline-flex items-center rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-indigo-300"
                                    title="View user"
                                >
                                    <Eye class="size-4" />
                                    <span class="sr-only">View</span>
                                </Link>
                                <Link
                                    v-if="can.edit"
                                    :href="`/users/${user.id}/edit`"
                                    class="inline-flex items-center rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-indigo-300"
                                    title="Edit user"
                                >
                                    <Edit3 class="size-4" />
                                    <span class="sr-only">Edit</span>
                                </Link>
                                <Link
                                    v-if="can.impersonate"
                                    :href="`/impersonate/${user.id}`"
                                    class="inline-flex items-center rounded-md p-2 text-blue-500 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-500/10"
                                    title="Impersonate user"
                                >
                                    <UserCog class="size-4" />
                                    <span class="sr-only">Impersonate</span>
                                </Link>
                                <button
                                    v-if="can.delete"
                                    type="button"
                                    class="inline-flex items-center rounded-md p-2 text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                                    title="Delete user"
                                    @click="destroy(user)"
                                >
                                    <Trash2 class="size-4" />
                                    <span class="sr-only">Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end print:hidden">
            <Pagination :links="paginationLinks" />
        </div>
    </div>
    </AppLayout>
</template>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 1.5cm;
    }

    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        height: auto !important;
    }

    .print-logo {
        max-height: 48px;
    }

    .print-divider {
        border: 0;
        border-top: 1px solid #cbd5f5;
        margin: 1rem auto 1.5rem;
        width: 100%;
    }

    .print-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .print-table thead tr {
        background-color: #f8fafc !important;
    }

    .print-table th,
    .print-table td {
        border: 1px solid #e2e8f0 !important;
        padding: 6px 8px !important;
        font-size: 12px !important;
        color: #0f172a !important;
        background-color: #ffffff !important;
    }

    .min-h-screen {
        min-height: auto !important;
    }

    main {
        page-break-after: avoid;
    }

    .liquidGlass-wrapper,
    .liquidGlass-content {
        background: #ffffff !important;
        box-shadow: none !important;
    }

    .liquidGlass-inner-shine {
        display: none !important;
    }

    .app-sidebar {
        display: none !important;
    }
}
</style>
