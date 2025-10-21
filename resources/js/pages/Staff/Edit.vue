<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import FileUploadField from '@/components/FileUploadField.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import InputError from '@/components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    staff: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        phone: string | null;
        job_title: string | null;
        status: 'active' | 'inactive';
        hire_date: string | null;
        avatar_url?: string | null;


        avatar_label?: string | null;


    };
    activity: ActivityEntry[];
}>();

const form = useForm({
    first_name: props.staff.first_name,
    last_name: props.staff.last_name,
    email: props.staff.email,
    phone: props.staff.phone ?? '',
    job_title: props.staff.job_title ?? '',
    status: props.staff.status,
    hire_date: props.staff.hire_date ?? '',
    user_id: null as number | null,
    avatar: null as File | null,
    remove_avatar: false,
});

const existingAvatarUrl = ref<string | null>(props.staff.avatar_url ?? null);
const existingAvatarLabel = ref<string | null>(props.staff.avatar_label ?? null);

const updateAvatar = (file: File | null) => {
    form.avatar = file;

    if (file) {
        form.remove_avatar = false;
    }
};

const clearExistingAvatar = () => {
    existingAvatarUrl.value = null;
    existingAvatarLabel.value = null;
    form.avatar = null;
    form.remove_avatar = true;
};

const submit = () => {
    form.put(`/staff/${props.staff.id}`, { forceFormData: true });
};
</script>

<template>
    <Head :title="`Edit ${staff.first_name}`" />

    <AppLayout :breadcrumbs="[{ title: 'Staff', href: '/staff' }, { title: staff.full_name ?? staff.first_name, href: `/staff/${staff.id}/edit` }]">
    <div class="flex flex-col gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                Edit staff member
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Update contact information or status for {{ staff.first_name }}.
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
                        :existing-url="existingAvatarUrl"
                        :existing-label="existingAvatarLabel"
                        @update:modelValue="updateAvatar"
                        @clear-existing="clearExistingAvatar"
                    />
                    <InputError :message="form.errors.avatar" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <GlassButton
                        size="sm"
                        variant="secondary"
                    >
                        <Link href="/staff" class="flex items-center gap-2">Cancel</Link>
                    </GlassButton>
                    <GlassButton size="sm" type="submit" :disabled="form.processing" variant="primary">
                        Save changes
                    </GlassButton>
                </div>
            </form>
       </GlassCard>

        <GlassCard variant="lite" content-class="space-y-4" :disable-shine="true">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    Recent activity
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Track changes applied to this staff profile.
                </p>
            </div>
            <ActivityTimeline :entries="activity" />
        </GlassCard>
    </div>
    </AppLayout>
</template>
