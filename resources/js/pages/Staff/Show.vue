<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Edit3, Printer } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted } from 'vue';

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
        full_name: string;
        first_name: string;
        last_name: string;
        email: string | null;
        phone: string | null;
        job_title: string | null;
        status: 'active' | 'inactive';
        hire_date: string | null;
        avatar_url?: string | null;
        avatar_label?: string | null;
        user: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    activity: ActivityEntry[];
    breadcrumbs: { title: string; href: string }[];
    print?: boolean;
}>();

const statusBadgeClass = computed(() =>
    props.staff.status === 'active'
        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
        : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
);

const printMode = computed(() => props.print ?? false);
let printTimer: number | undefined;

const printTimestamp = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date());

const buildPrintTitle = () => `Asset Management - Staff Profile - ${props.staff.full_name}`;

const triggerPrint = () => {
    const originalTitle = document.title;
    document.title = buildPrintTitle();
    window.print();
    document.title = originalTitle;
};

const closeAfterPrint = () => {
    if (printMode.value && window.opener && !window.opener.closed) {
        window.close();
    }
};

onMounted(() => {
    if (printMode.value) {
        printTimer = window.setTimeout(() => {
            triggerPrint();
        }, 150);
        window.addEventListener('afterprint', closeAfterPrint);
    }
});

onBeforeUnmount(() => {
    if (printTimer) {
        window.clearTimeout(printTimer);
    }

    window.removeEventListener('afterprint', closeAfterPrint);
});

const printRecord = () => {
    triggerPrint();
};
</script>

<template>
    <Head :title="`Staff - ${props.staff.full_name}`" />

    <AppLayout :breadcrumbs="props.breadcrumbs">
        <div class="space-y-6">
            <div class="liquidGlass-wrapper print:hidden">
                <span class="liquidGlass-inner-shine" aria-hidden="true" />
                <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                            Staff profile
                        </h1>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Detailed view for {{ props.staff.full_name }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <GlassButton as="span" size="sm" variant="secondary">
                            <Link href="/staff" class="flex items-center gap-2">
                                <ArrowLeft class="size-4" />
                                <span>Back to list</span>
                            </Link>
                        </GlassButton>

                        <GlassButton as="span" size="sm" variant="primary">
                            <Link :href="`/staff/${props.staff.id}/edit`" class="flex items-center gap-2">
                                <Edit3 class="size-4" />
                                <span>Edit</span>
                            </Link>
                        </GlassButton>

                        <GlassButton size="sm" type="button" class="flex items-center gap-2" variant="warning" @click="printRecord">
                            <Printer class="size-4" />
                            <span>Print</span>
                        </GlassButton>
                    </div>
            </div>
        </div>

        <div class="hidden print:block text-center text-slate-800">
            <img src="/images/asset-logo.svg" alt="Asset Management" class="mx-auto mb-3 h-12 w-auto print-logo" />
            <h1 class="text-xl font-semibold">Asset Management</h1>
            <p class="text-sm">Staff Profile: {{ props.staff.full_name }}</p>
            <p class="text-xs text-slate-500">Printed {{ printTimestamp }}</p>
            <hr class="print-divider" />
        </div>

            <GlassCard padding="p-0" class="print:shadow-none print:bg-white print:border">
                <div class="overflow-hidden rounded-xl border border-slate-200/70 bg-white/80 dark:border-slate-800/60 dark:bg-slate-900/60 print:border print:bg-white">
                    <div class="flex flex-col gap-6 p-6 md:flex-row md:items-start">
                        <div class="flex flex-col items-center gap-3 md:w-1/4">
                            <div class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-white shadow-md dark:border-slate-700 dark:bg-slate-950">
                                <img
                                    v-if="props.staff.avatar_url"
                                    :src="props.staff.avatar_url"
                                    :alt="props.staff.full_name"
                                    class="h-full w-full object-cover"
                                />
                                <span v-else class="text-3xl font-semibold text-slate-500 dark:text-slate-400">
                                    {{ props.staff.first_name.charAt(0) }}{{ props.staff.last_name.charAt(0) }}
                                </span>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                    {{ props.staff.full_name }}
                                </p>
                                <span
                                    class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="statusBadgeClass"
                                >
                                    {{ props.staff.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid flex-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                    Contact
                                </p>
                                <div class="space-y-2 rounded-lg border border-slate-200/70 bg-white/70 p-4 text-sm shadow-sm dark:border-slate-800/50 dark:bg-slate-900/60">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500">Email</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ props.staff.email ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500">Phone</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ props.staff.phone ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                    Assignment
                                </p>
                                <div class="space-y-2 rounded-lg border border-slate-200/70 bg-white/70 p-4 text-sm shadow-sm dark:border-slate-800/50 dark:bg-slate-900/60">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500">Job title</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ props.staff.job_title ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500">Hire date</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ props.staff.hire_date ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500">Linked user</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">
                                            <template v-if="props.staff.user">
                                                {{ props.staff.user.name }}
                                                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ props.staff.user.email }}</span>
                                            </template>
                                            <span v-else>-</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </GlassCard>

            <GlassCard
                v-if="props.activity.length"
                variant="lite"
                content-class="space-y-4"
                :disable-shine="true"
                class="print:shadow-none print:bg-white print:border"
            >
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                        Recent activity
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Audit trail for updates to this staff profile.
                    </p>
                </div>
                <ActivityTimeline :entries="props.activity" />
            </GlassCard>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 1.5cm;
    }

    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    .print-logo {
        max-height: 48px;
    }

    .print-divider {
        border: 0;
        border-top: 1px solid #cbd5f5;
        margin: 1rem auto 1.5rem;
        width: 100%;
    }

    .liquidGlass-wrapper,
    .liquidGlass-content {
        background: #ffffff !important;
        box-shadow: none !important;
    }

    .liquidGlass-inner-shine {
        display: none !important;
    }

    .rounded-lg,
    .rounded-xl {
        background: #ffffff !important;
        box-shadow: none !important;
        border-color: #e2e8f0 !important;
    }

    .text-slate-500,
    .text-slate-400,
    .text-slate-600 {
        color: #334155 !important;
    }
}
</style>
