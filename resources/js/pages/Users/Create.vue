<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import UserForm from './Partials/UserForm.vue';

interface StaffOption {
    id: number;
    label: string;
    status: string;
    linked_user_id: number | null;
    linked_to_current_user: boolean;
}

const props = defineProps<{
    roles: string[];
    permissions: string[];
    staff: StaffOption[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    account_status: 'active',
    account_type: 'internal',
    roles: [] as string[],
    permissions: [] as string[],
    staff_id: null as number | null,
});

const canSubmit = computed(() => !form.processing);

const submit = () => {
    form.post('/users');
};
</script>

<template>
    <Head title="Create User" />

    <AppLayout :breadcrumbs="[{ title: 'Users', href: '/users' }, { title: 'Create', href: '/users/create' }]" >
    <div class="space-y-6">
        <div class="liquidGlass-wrapper">
            <span class="liquidGlass-inner-shine" aria-hidden="true" />
            <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        Create user
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Invite a teammate and assign roles and permissions.
                    </p>
                </div>
                <GlassButton as="span" size="sm" variant="secondary">
                    <Link href="/users" class="flex items-center gap-2">Back to list</Link>
                </GlassButton>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <UserForm
                :form="form"
                :roles="roles"
                :permissions="permissions"
                :staff="staff"
            />

            <div class="flex justify-end">
                <GlassButton type="submit" :disabled="!canSubmit" variant="primary">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Create user</span>
                </GlassButton>
            </div>
        </form>
    </div>
    </AppLayout>
</template>
