++ resources/js/pages/Notifications/Index.vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { AlertCircle, CheckCircle2, Info, MessageSquare } from 'lucide-vue-next';

interface NotificationPayload {
    id: string;
    type: string;
    data: {
        sender_name?: string;
        message_preview?: string;
        title?: string;
        body?: string;
        url?: string | null;
    };
    read_at: string | null;
    created_at: string;
}

const props = defineProps<{
    notifications: NotificationPayload[];
    unread_count: number;
}>();

const notifications = ref<NotificationPayload[]>(props.notifications ?? []);
const unreadCount = ref<number>(props.unread_count ?? 0);
const isMarkingAll = ref(false);
const isMarkingSingle = ref<string | null>(null);

const resolveIcon = (type: string) => {
    const lower = type.toLowerCase();

    if (lower.includes('message')) return MessageSquare;
    if (lower.includes('task')) return CheckCircle2;
    if (lower.includes('alert') || lower.includes('warning')) return AlertCircle;

    return Info;
};

const summaryLine = (notification: NotificationPayload) =>
    notification.data?.message_preview ??
    notification.data?.body ??
    'Tap to view details.';

const headline = (notification: NotificationPayload) =>
    notification.data?.sender_name ??
    notification.data?.title ??
    'Notification';

const markAsRead = async (notification: NotificationPayload) => {
    if (notification.read_at) {
        navigate(notification);
        return;
    }

    try {
        isMarkingSingle.value = notification.id;
        await axios.post(`/notifications/${notification.id}/read`);

        notification.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    } finally {
        isMarkingSingle.value = null;
        navigate(notification);
    }
};

const markAllRead = async () => {
    if (unreadCount.value === 0) {
        return;
    }

    try {
        isMarkingAll.value = true;
        await axios.post('/notifications/read-all');
        notifications.value = notifications.value.map((notification) => ({
            ...notification,
            read_at: notification.read_at ?? new Date().toISOString(),
        }));
        unreadCount.value = 0;
    } finally {
        isMarkingAll.value = false;
    }
};

const navigate = (notification: NotificationPayload) => {
    if (!notification.data?.url) {
        return;
    }

    window.location.href = notification.data.url;
};
</script>

<template>
    <Head title="Notifications" />

    <AppLayout :breadcrumbs="[{ title: 'Notifications', href: '/notifications' }]">
        <div class="space-y-6">
            <GlassCard variant="lite" content-class="flex flex-col gap-3 justify-between md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        Notifications
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Stay updated with alert, task, and message activity across the team.
                    </p>
                </div>
                <div class="flex gap-2">
                    <GlassButton
                        v-if="unreadCount > 0"
                        variant="secondary"
                        size="sm"
                        type="button"
                        :disabled="isMarkingAll"
                        @click="markAllRead"
                    >
                        Mark all read
                    </GlassButton>
                    <GlassButton
                        as="span"
                        variant="primary"
                        size="sm"
                    >
                        <Link href="/settings/profile" class="flex items-center gap-2">
                            Manage preferences
                        </Link>
                    </GlassButton>
                </div>
            </GlassCard>

            <GlassCard variant="lite" padding="p-0">
                <div class="overflow-hidden rounded-xl border border-slate-200/70 bg-white/80 dark:border-slate-800/60 dark:bg-slate-900/60">
                    <ul v-if="notifications.length" class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                        <li
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="flex flex-col gap-2 px-5 py-4 text-sm transition hover:bg-slate-50/70 dark:hover:bg-slate-800/60 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20">
                                    <component :is="resolveIcon(notification.type)" class="h-5 w-5" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ headline(notification) }}
                                    </p>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">
                                        {{ summaryLine(notification) }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">
                                        {{ new Date(notification.created_at).toLocaleString() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-start">
                                <GlassButton
                                    size="sm"
                                    variant="secondary"
                                    type="button"
                                    :disabled="isMarkingSingle === notification.id"
                                    @click="markAsRead(notification)"
                                >
                                    <span v-if="notification.read_at">View</span>
                                    <span v-else-if="isMarkingSingle === notification.id">Marking…</span>
                                    <span v-else>Mark read</span>
                                </GlassButton>
                            </div>
                        </li>
                    </ul>
                    <div v-else class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                        You don’t have any notifications yet. Invite your team or enable alerts to start receiving updates.
                    </div>
                </div>
            </GlassCard>
        </div>
    </AppLayout>
</template>
