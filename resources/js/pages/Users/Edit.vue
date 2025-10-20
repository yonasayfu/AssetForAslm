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

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: [...props.user.roles],
    permissions: [...props.user.permissions],
    staff_id: props.user.staff_id,
});

const canSubmit = computed(() => !form.processing);

const submit = () => {
    form.put(`/users/${props.user.id}`);
};

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
                    <GlassButton
                        size="sm"
                        class="bg-slate-200/80 text-slate-700 hover:bg-slate-300 dark:bg-slate-800/60 dark:text-slate-200"
                        as="span"
                    >
                        <Link href="/users">Back to list</Link>
                    </GlassButton>
                    <GlassButton
                        size="sm"
                        class="bg-red-500/15 text-red-600 hover:bg-red-500/25 dark:bg-red-500/20 dark:text-red-200"
                        type="button"
                        @click="destroyUser"
                    >
                        Delete
                    </GlassButton>
                </div>
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
                <GlassButton type="submit" :disabled="!canSubmit">
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
