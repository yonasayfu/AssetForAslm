<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FileUploadField from '@/components/FileUploadField.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import InputError from '@/components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    job_title: '',
    status: 'active',
    hire_date: '',
    user_id: null as number | null,
    avatar: null as File | null,
});

const submit = () => {
    form.post('/staff', { forceFormData: true });
};
</script>

<template>
    <Head title="New Staff" />

    <AppLayout :breadcrumbs="[{ title: 'Staff', href: '/staff' }, { title: 'Create', href: '/staff/create' }]">
    <div class="flex flex-col gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                Add staff member
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Store directory details and link an existing user account later if needed.
            </p>
        </div>

        <GlassCard>
            <form class="space-y-5" @submit.prevent="submit">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            First name
                        </label>
                        <input
                            v-model="form.first_name"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40"
                            autocomplete="given-name"
                        />
                        <InputError :message="form.errors.first_name" class="mt-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Last name
                        </label>
                        <input
                            v-model="form.last_name"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40"
                            autocomplete="family-name"
                        />
                        <InputError :message="form.errors.last_name" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40"
                            autocomplete="email"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Phone
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40"
                            autocomplete="tel"
                        />
                        <InputError :message="form.errors.phone" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Job title
                        </label>
                        <input
                            v-model="form.job_title"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40"
                        />
                        <InputError :message="form.errors.job_title" class="mt-2" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Status
                            </label>
                            <select
                                v-model="form.status"
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Hire date
                            </label>
                            <input
                                v-model="form.hire_date"
                                type="date"
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40"
                            />
                            <InputError :message="form.errors.hire_date" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div>
                    <FileUploadField
                        label="Profile photo"
                        hint="Images are stored under storage/app/public/staff/avatars."
                        accept="image/*"
                        variant="image"
                        :model-value="form.avatar"
                        @update:modelValue="(file) => (form.avatar = file)"
                        @clear-existing="() => {}"
                    />
                    <InputError :message="form.errors.avatar" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <GlassButton
                        size="sm"
                        variant="secondary"
                    >
                        <Link href="/staff" class="flex items-center gap-2">
                            Cancel
                        </Link>
                    </GlassButton>
                    <GlassButton size="sm" type="submit" :disabled="form.processing" variant="primary">
                        Save
                    </GlassButton>
                </div>
            </form>
        </GlassCard>
    </div>
    </AppLayout>
</template>
