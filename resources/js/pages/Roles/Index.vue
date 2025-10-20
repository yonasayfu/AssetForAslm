<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import { confirmDialog } from '@/lib/confirm';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface RoleSummary {
    id: number;
    name: string;
    permissions: string[];
}

const props = defineProps<{
    roles: RoleSummary[];
    permissions: string[];
    can: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}>();

const roles = computed(() => props.roles ?? []);
const permissions = computed(() => props.permissions ?? []);

const remove = async (role: RoleSummary) => {
    if (!(await confirmDialog({
        title: 'Delete role?',
        message: `Removing ${role.name} will detach the permissions from any users assigned to it.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
    }))) {
        return;
    }

    router.delete(`/roles/${role.id}`);
};
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="[{ title: 'Roles', href: '/roles' }]">
    <div class="space-y-6">
        <div class="liquidGlass-wrapper">
            <span class="liquidGlass-inner-shine" aria-hidden="true" />
            <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        Role management
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Define access profiles and attach permissions to your team.
                    </p>
                </div>

                <div v-if="can.create">
                    <GlassButton as="span">
                        <Link href="/roles/create">
                            <span>Create role</span>
                        </Link>
                    </GlassButton>
                </div>
            </div>
        </div>

        <GlassCard
            variant="lite"
            padding="px-6 py-5"
            content-class="space-y-4"
        >
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                        Available permissions
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Permissions seeded in the system. Attach them to roles as needed.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="permission in permissions"
                        :key="permission"
                        class="inline-flex items-center rounded-full bg-slate-200/70 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-800/60 dark:text-slate-200"
                    >
                        {{ permission }}
                    </span>
                </div>
            </div>
        </GlassCard>

        <div class="overflow-hidden rounded-xl border border-slate-200/70 bg-white/70 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/50">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50/80 dark:bg-slate-900/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Permissions
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/90 dark:divide-slate-800 dark:bg-slate-950/40">
                    <tr v-if="roles.length === 0">
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-300">
                            No roles created yet. Start by defining a custom role.
                        </td>
                    </tr>
                    <tr v-for="role in roles" v-else :key="role.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/50">
                        <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ role.name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <div v-if="role.permissions.length" class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in role.permissions"
                                    :key="permission"
                                    class="inline-flex items-center rounded-full bg-emerald-100/80 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                            <span v-else class="text-xs text-slate-400 dark:text-slate-500">
                                No direct permissions (inherits from defaults)
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <div class="flex justify-end gap-2">
                                <GlassButton
                                    v-if="can.edit"
                                    size="sm"
                                    as="span"
                                >
                                    <Link :href="`/roles/${role.id}/edit`">
                                        Edit
                                    </Link>
                                </GlassButton>
                                <GlassButton
                                    v-if="can.delete"
                                    size="sm"
                                    class="bg-red-500/10 text-red-600 hover:bg-red-500/20 dark:bg-red-500/20 dark:text-red-200"
                                    type="button"
                                    @click="remove(role)"
                                >
                                    Delete
                                </GlassButton>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </AppLayout>
</template>
