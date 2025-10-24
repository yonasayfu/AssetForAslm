<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        account_status: string;
        account_type: string;
        email_verified: boolean;
        approved_at: string | null;
    };
}>();

const statusLabels: Record<string, string> = {
    pending: 'Pending approval',
    active: 'Active',
    suspended: 'Suspended',
};
const accountTypeLabels: Record<string, string> = {
    external: 'External collaborator',
    internal: 'Internal staff',
};

const needsVerification = computed(() => !props.user.email_verified);
const statusLabel = computed(() => statusLabels[props.user.account_status] ?? props.user.account_status);
const accountTypeLabel = computed(() => accountTypeLabels[props.user.account_type] ?? props.user.account_type);
const isSuspended = computed(() => props.user.account_status === 'suspended');

const headingTitle = computed(() =>
    isSuspended.value ? 'Your account is suspended' : 'Your account is almost ready',
);
const headingDescription = computed(() =>
    isSuspended.value
        ? 'Access is currently restricted. Contact an administrator to review your account.'
        : 'We need to verify your email and confirm your access before you can continue.',
);
const statusMessage = computed(() =>
    isSuspended.value
        ? 'Your account has been suspended by an administrator. Please reach out to confirm next steps or to request reinstatement.'
        : 'An administrator will review your details and link you to the appropriate staff record. Once approved you will receive a notification and gain access to the full application.',
);

const resendVerification = () => {
    router.post('/email/verification-notification', {}, { preserveScroll: true });
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Account pending approval" />

    <AuthLayout
        :title="headingTitle"
        :description="headingDescription"
        variant="card"
    >
        <div class="space-y-6">
            <div class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <p>
                    Welcome, <span class="font-semibold">{{ user.name }}</span>.
                </p>
                <p>
                    Your account is currently marked as
                    <span class="font-semibold text-indigo-600 dark:text-indigo-300">{{ statusLabel }}</span>
                    and classified as
                    <span class="font-semibold text-indigo-600 dark:text-indigo-300">{{ accountTypeLabel }}</span>.
                </p>
                <p>{{ statusMessage }}</p>
            </div>

            <div v-if="needsVerification" class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-800 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-100">
                <p class="font-medium">Email verification required</p>
                <p class="mt-1 text-xs opacity-90">
                    We have sent a verification link to <span class="font-semibold">{{ user.email }}</span>. Please click
                    the link in that email to confirm your address. If you didn’t receive it, you can request another.
                </p>
                <GlassButton class="mt-3" size="sm" variant="secondary" @click="resendVerification">
                    Resend verification email
                </GlassButton>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white/90 p-4 text-sm shadow-sm dark:border-slate-700/80 dark:bg-slate-900/60">
                <p class="font-medium text-slate-900 dark:text-slate-100">
                    Need access right away?
                </p>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                    Reach out to your system administrator and share the email address you used to register. They can
                    assign you to a staff profile and activate your account in minutes.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    You can sign out safely while you wait; we’ll email you as soon as everything is ready.
                </p>
                <GlassButton variant="ghost" size="sm" @click="logout">
                    Sign out
                </GlassButton>
            </div>
        </div>
    </AuthLayout>
</template>
