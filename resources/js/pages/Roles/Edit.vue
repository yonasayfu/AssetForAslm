<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import RoleForm from './Partials/RoleForm.vue';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        permissions: string[];
    };
    permissions: string[];
}>();

const form = useForm({
    name: props.role.name,
    permissions: [...props.role.permissions],
});

const title = computed(() => `Edit ${props.role.name}`);

const submit = () => {
    form.put(`/roles/${props.role.id}`);
};
</script>

<template>
    <Head :title="title" />

    <AppLayout :breadcrumbs="[{ title: 'Roles', href: '/roles' }, { title: props.role.name, href: `/roles/${props.role.id}/edit` }]">
    <div class="space-y-6">
        <div class="liquidGlass-wrapper">
            <span class="liquidGlass-inner-shine" aria-hidden="true" />
            <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Update the permissions assigned to this role.
                    </p>
                </div>
                <div>
                    <GlassButton variant="ghost" size="sm" as="span">
                        <Link href="/roles">
                            Back to list
                        </Link>
                    </GlassButton>
                </div>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <RoleForm :form="form" :permissions="props.permissions" />

            <div class="flex justify-end">
                <GlassButton type="submit" :disabled="form.processing">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Update role</span>
                </GlassButton>
            </div>
        </form>
    </div>
    </AppLayout>
</template>
