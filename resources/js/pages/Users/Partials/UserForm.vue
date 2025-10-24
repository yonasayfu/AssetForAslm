<script setup lang="ts">
import GlassCard from '@/components/GlassCard.vue';
import InputError from '@/components/InputError.vue';
import { computed } from 'vue';

interface StaffOption {
    id: number;
    label: string;
    status: string;
    linked_user_id: number | null;
    linked_to_current_user: boolean;
}

const props = defineProps<{
    form: {
        name: string;
        email: string;
        password?: string;
        password_confirmation?: string;
        account_status: string;
        account_type: string;
        roles: string[];
        permissions: string[];
        staff_id: number | null;
    };
    roles: string[];
    permissions: string[];
    staff: StaffOption[];
    isEdit?: boolean;
}>();

const staffOptions = computed<StaffOption[]>(() => props.staff ?? []);

const accountStatusOptions = [
    {
        value: 'pending',
        label: 'Pending approval',
        description: 'Access is limited to the pending screen until an admin approves.',
    },
    {
        value: 'active',
        label: 'Active',
        description: 'User can access the application according to their roles.',
    },
    {
        value: 'suspended',
        label: 'Suspended',
        description: 'Login is allowed, but access is redirected to a suspension notice.',
    },
];

const accountTypeOptions = [
    {
        value: 'external',
        label: 'External collaborator',
        description: 'Use for contractors or customers awaiting staff onboarding.',
    },
    {
        value: 'internal',
        label: 'Internal staff',
        description: 'Use for employees who should appear in staff workflows.',
    },
];

const selectedStatusOption = computed(() =>
    accountStatusOptions.find((option) => option.value === props.form.account_status),
);
const selectedTypeOption = computed(() =>
    accountTypeOptions.find((option) => option.value === props.form.account_type),
);

const toggleRole = (role: string) => {
    const index = props.form.roles.indexOf(role);
    if (index === -1) {
        props.form.roles.push(role);
    } else {
        props.form.roles.splice(index, 1);
    }
};

const hasRole = (role: string) => props.form.roles.includes(role);

const togglePermission = (permission: string) => {
    const index = props.form.permissions.indexOf(permission);
    if (index === -1) {
        props.form.permissions.push(permission);
    } else {
        props.form.permissions.splice(index, 1);
    }
};

const hasPermission = (permission: string) =>
    props.form.permissions.includes(permission);
</script>

<template>
    <div class="space-y-6">
        <GlassCard>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Full name
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        autocomplete="name"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100"
                    />
                    <InputError :message="$page.props.errors?.name" class="mt-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Email
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100"
                    />
                    <InputError :message="$page.props.errors?.email" class="mt-2" />
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ isEdit ? 'New password (optional)' : 'Password' }}
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100"
                    />
                    <InputError :message="$page.props.errors?.password" class="mt-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ isEdit ? 'Confirm password' : 'Confirm password' }}
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100"
                    />
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Account status
                    </label>
                    <select
                        v-model="form.account_status"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100"
                    >
                        <option
                            v-for="option in accountStatusOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ selectedStatusOption?.description }}
                    </p>
                    <InputError :message="$page.props.errors?.account_status" class="mt-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Account type
                    </label>
                    <select
                        v-model="form.account_type"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100"
                    >
                        <option
                            v-for="option in accountTypeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ selectedTypeOption?.description }}
                    </p>
                    <InputError :message="$page.props.errors?.account_type" class="mt-2" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                    Link to staff profile
                </label>
                <select
                    v-model="form.staff_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100"
                >
                    <option :value="null">No linked staff profile</option>
                    <option
                        v-for="option in staffOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.label }}
                        <span v-if="option.linked_user_id && !option.linked_to_current_user">
                            (currently linked)
                        </span>
                    </option>
                </select>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Selecting a staff profile will move it to this user if already linked elsewhere.
                </p>
                <InputError :message="$page.props.errors?.staff_id" class="mt-2" />
            </div>
        </GlassCard>

        <GlassCard>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">
                Roles
            </h2>
            <div class="mt-3 grid gap-2 md:grid-cols-2">
                <label
                    v-for="role in roles"
                    :key="role"
                    class="flex items-center gap-2 rounded-lg border border-slate-200/70 bg-white/70 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-indigo-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200"
                >
                    <input
                        type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        :checked="hasRole(role)"
                        @change="toggleRole(role)"
                    />
                    <span>{{ role }}</span>
                </label>
            </div>
        </GlassCard>

        <GlassCard>
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">
                    Direct permissions
                </h2>
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Roles already assign permissions; use these for overrides.
                </p>
            </div>
            <div class="mt-3 grid gap-2 md:grid-cols-2">
                <label
                    v-for="permission in permissions"
                    :key="permission"
                    class="flex items-center gap-2 rounded-lg border border-slate-200/70 bg-white/70 px-3 py-2 text-sm text-slate-600 transition hover:border-indigo-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300"
                >
                    <input
                        type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        :checked="hasPermission(permission)"
                        @change="togglePermission(permission)"
                    />
                    <span>{{ permission }}</span>
                </label>
            </div>
        </GlassCard>
    </div>
</template>
