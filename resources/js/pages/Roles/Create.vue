<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import RoleForm from './Partials/RoleForm.vue';

const props = defineProps<{
    permissions: string[];
}>();

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const submit = () => {
    form.post('/roles');
};
</script>

<template>
    <Head title="Create role" />

    <AppLayout :breadcrumbs="[{ title: 'Roles', href: '/roles' }, { title: 'Create role', href: '/roles/create' }]">
    <div class="space-y-6">
        <div class="liquidGlass-wrapper">
            <span class="liquidGlass-inner-shine" aria-hidden="true" />
            <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        Create role
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Combine permissions into a reusable access profile.
                    </p>
                </div>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <RoleForm :form="form" :permissions="props.permissions" />

            <div class="flex justify-end">
                <GlassButton type="submit" :disabled="form.processing">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Create role</span>
                </GlassButton>
            </div>
        </form>
    </div>
    </AppLayout>
</template>
