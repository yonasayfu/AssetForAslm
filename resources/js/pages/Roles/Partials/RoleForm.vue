<script setup lang="ts">
import GlassCard from '@/components/GlassCard.vue';
import type { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type RoleForm = ReturnType<typeof useForm<{
    name: string;
    permissions: string[];
}>>;

const props = defineProps<{
    form: RoleForm;
    permissions: string[];
    submittingLabel?: string;
}>();

const permissions = computed(() => props.permissions ?? []);

const isChecked = (permission: string) => props.form.permissions.includes(permission);

const togglePermission = (permission: string) => {
    if (isChecked(permission)) {
        props.form.permissions = props.form.permissions.filter((item) => item !== permission);
    } else {
        props.form.permissions = [...props.form.permissions, permission];
    }
};
</script>

<template>
    <div class="space-y-6">
        <GlassCard
            variant="lite"
            padding="px-6 py-5"
            content-class="space-y-4"
        >
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                    Role name
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="mt-2 w-full rounded-md border border-transparent bg-white/80 px-3 py-2 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-300 focus:ring-0 dark:bg-slate-900/70 dark:text-slate-200"
                    placeholder="e.g. Operations Manager"
                />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-500">
                    {{ form.errors.name }}
                </p>
            </div>
        </GlassCard>

        <GlassCard
            variant="lite"
            padding="px-6 py-5"
            content-class="space-y-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                        Permissions
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Choose which capabilities this role should grant.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center rounded-full border border-transparent bg-slate-200/70 px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-300 dark:bg-slate-800/60 dark:text-slate-200 dark:hover:bg-slate-700"
                    @click="form.permissions = [...permissions]"
                >
                    Select all
                </button>
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                <label
                    v-for="permission in permissions"
                    :key="permission"
                    class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent bg-white/70 px-3 py-2 text-sm text-slate-700 transition hover:border-indigo-200 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-indigo-400/40"
                >
                    <input
                        :checked="isChecked(permission)"
                        class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600"
                        type="checkbox"
                        @change="togglePermission(permission)"
                    />
                    <span>{{ permission }}</span>
                </label>
            </div>
            <p v-if="form.errors.permissions" class="text-sm text-red-500">
                {{ form.errors.permissions }}
            </p>
        </GlassCard>
    </div>
</template>
