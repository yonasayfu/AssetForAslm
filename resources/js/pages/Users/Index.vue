<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import Pagination from '@/components/Pagination.vue';
import { confirmDialog } from '@/lib/confirm';
import { useTableFilters } from '@/composables/useTableFilters';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed } from 'vue';

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
    };
}>();

const tableFilters = useTableFilters({
    route: '/users',
    initial: {
        search: props.filters?.search ?? '',
        per_page: props.filters?.per_page ?? 10,
    },
});

const { search, perPage } = tableFilters;

const users = computed<UserSummary[]>(() => props.users?.data ?? []);
const stats = computed<StatCard[]>(() => props.stats ?? []);
const hasResults = computed<boolean>(() => users.value.length > 0);
const paginationLinks = computed(() => props.users?.links ?? []);

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
        <div class="liquidGlass-wrapper">
            <span class="liquidGlass-inner-shine" aria-hidden="true" />
            <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        User management
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Manage access controls, roles, and permissions for your team.
                    </p>
                </div>

                <div v-if="can.create" class="flex items-center gap-2">
                    <GlassButton as="span">
                        <Link href="/users/create" class="flex items-center gap-2">
                            <span>New user</span>
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

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
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
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/70 bg-white/70 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/50">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
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
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/90 dark:divide-slate-800 dark:bg-slate-950/40">
                    <tr v-if="!hasResults">
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-300">
                            No users found. Create your first account to get started.
                        </td>
                    </tr>
                    <tr v-for="user in users" v-else :key="user.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/50">
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                {{ user.name }}
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

                        <td class="px-5 py-4 text-right text-sm">
                            <div class="flex justify-end gap-2">
                                <GlassButton
                                    v-if="can.edit"
                                    size="sm"
                                    as="span"
                                >
                                    <Link :href="`/users/${user.id}/edit`">
                                        Edit
                                    </Link>
                                </GlassButton>
                                <GlassButton
                                    v-if="can.delete"
                                    size="sm"
                                    class="bg-red-500/10 text-red-600 hover:bg-red-500/20 dark:bg-red-500/20 dark:text-red-200"
                                    type="button"
                                    @click="destroy(user)"
                                >
                                    Delete
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
