<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import { confirmDialog } from '@/lib/confirm';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import UserForm from './Partials/UserForm.vue';

interface StaffOption {
    id: number;
    label: string;
    status: string;
    linked_user_id: number | null;
    linked_to_current_user: boolean;
}

interface EditableUser {
    id: number;
    name: string;
    email: string;
    account_status: string;
    account_type: string;
    approved_at: string | null;
    approved_by: string | null;
    roles: string[];
    permissions: string[];
    staff_id: number | null;
}

type ActivityEntry = {
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
    user: EditableUser;
    roles: string[];
    permissions: string[];
    staff: StaffOption[];
    activity: ActivityEntry[];
}>();

const statusLabels: Record<string, string> = {
    pending: 'Pending approval',
    active: 'Active',
    suspended: 'Suspended',
};
const accountTypeLabels: Record<string, string> = {
    internal: 'Internal',
    external: 'External',
};

const formatStatus = (status: string): string => statusLabels[status] ?? status;
const formatAccountType = (type: string): string => accountTypeLabels[type] ?? type;

const approvedFormatter = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    account_status: props.user.account_status,
    account_type: props.user.account_type,
    roles: [...props.user.roles],
    permissions: [...props.user.permissions],
    staff_id: props.user.staff_id,
});

const canSubmit = computed(() => !form.processing);

const submit = () => {
    form.put(`/users/${props.user.id}`);
};

const approvalSummary = computed(() => {
    if (!props.user.approved_at) {
        return 'Not yet approved';
    }

    const date = new Date(props.user.approved_at);
    const formatted = Number.isNaN(date.getTime()) ? props.user.approved_at : approvedFormatter.format(date);

    return props.user.approved_by ? `${formatted} - ${props.user.approved_by}` : formatted;
});

const destroyUser = async () => {
    const accepted = await confirmDialog({
        title: 'Delete user?',
        message: 'This will remove the account and unlink any staff profile.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
    });

    if (!accepted) {
        return;
    }

    router.delete(`/users/${props.user.id}`);
};
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <AppLayout :breadcrumbs="[{ title: 'Users', href: '/users' }, { title: user.name, href: `/users/${user.id}/edit` }]">
    <div class="space-y-6">
        <div class="liquidGlass-wrapper">
            <span class="liquidGlass-inner-shine" aria-hidden="true" />
            <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        Edit user
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Update account details, roles, and permissions.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <GlassButton size="sm" variant="secondary" as="span">
                        <Link href="/users" class="flex items-center gap-2">Back to list</Link>
                    </GlassButton>
                    <GlassButton
                        size="sm"
                        variant="danger"
                        type="button"
                        @click="destroyUser"
                    >
                        Delete
                    </GlassButton>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/80 bg-white/80 p-4 text-sm shadow-sm dark:border-slate-700/80 dark:bg-slate-900/60">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                Current access status
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-100">
                    {{ formatStatus(props.user.account_status) }}
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ formatAccountType(props.user.account_type) }}
                </span>
                <span class="text-xs text-slate-400 dark:text-slate-500">
                    {{ approvalSummary }}
                </span>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <UserForm
                :form="form"
                :roles="roles"
                :permissions="permissions"
                :staff="staff"
                is-edit
            />

            <div class="flex justify-end">
                <GlassButton type="submit" :disabled="!canSubmit" variant="primary">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Update user</span>
                </GlassButton>
            </div>
        </form>

        <GlassCard variant="lite" content-class="space-y-4" :disable-shine="true">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    Recent activity
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Role assignments and profile updates applied to this account.
                </p>
            </div>
            <ActivityTimeline :entries="activity" />
        </GlassCard>
    </div>
    </AppLayout>
</template>
