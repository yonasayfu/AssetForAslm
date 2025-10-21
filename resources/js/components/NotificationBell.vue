<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { AlertCircle, Bell, CheckCircle2, Info, MessageSquare } from 'lucide-vue-next';

interface NotificationPayload {
    id: string;
    type: string;
    data: {
        sender_name?: string;
        message_preview?: string;
        url?: string | null;
        type?: string;
        title?: string;
        body?: string;
    };
    read_at: string | null;
}

const notifications = ref<NotificationPayload[]>([]);
const unreadCount = ref(0);
const isOpen = ref(false);
const triggerRef = ref<HTMLButtonElement | null>(null);
const dropdownRef = ref<HTMLDivElement | null>(null);
let pollingInterval: number | null = null;

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

        notifications.value = response.data.notifications ?? [];
        unreadCount.value = response.data.unread_count ?? 0;
    } catch (error) {
        console.error('Failed to fetch notifications', error);
    }
};

const markAsRead = async (notificationId: string) => {
    try {
        await axios.post(`/notifications/${notificationId}/read`);

        const target = notifications.value.find(({ id }) => id === notificationId);
        if (target && !target.read_at) {
            target.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    } catch (error) {
        console.error('Failed to mark notification as read', error);
    }
};

const markAllRead = async () => {
    if (!unreadCount.value) {
        return;
    }

    try {
        await axios.post('/notifications/read-all');
        notifications.value = notifications.value.map((notification) => ({
            ...notification,
            read_at: notification.read_at ?? new Date().toISOString(),
        }));
        unreadCount.value = 0;
    } catch (error) {
        console.error('Failed to mark all notifications as read', error);
    }
};

const resolveIcon = (type: string) => {
    const lower = type.toLowerCase();

    if (lower.includes('message')) return MessageSquare;
    if (lower.includes('task')) return CheckCircle2;
    if (lower.includes('alert') || lower.includes('warning')) return AlertCircle;

    return Info;
};

const handleNotificationClick = async (notification: NotificationPayload) => {
    isOpen.value = false;

    const url = notification.data?.url;

    if (url) {
        router.visit(url, {
            preserveScroll: true,
            onFinish: () => markAsRead(notification.id),
            onError: () => markAsRead(notification.id),
        });

        return;
    }

    await markAsRead(notification.id);
};

const handleDocumentClick = (event: MouseEvent) => {
    if (!isOpen.value) {
        return;
    }

    const target = event.target as Node;
    const withinTrigger = triggerRef.value?.contains(target);
    const withinDropdown = dropdownRef.value?.contains(target);

    if (!withinTrigger && !withinDropdown) {
        isOpen.value = false;
    }
};

onMounted(() => {
    fetchNotifications();
    pollingInterval = window.setInterval(fetchNotifications, 30_000);
    document.addEventListener('click', handleDocumentClick);
});

onUnmounted(() => {
    if (pollingInterval) {
        window.clearInterval(pollingInterval);
    }

    document.removeEventListener('click', handleDocumentClick);
});
</script>

<template>
    <div class="relative">
        <button
            ref="triggerRef"
            type="button"
            class="relative flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/40 text-slate-600 shadow-sm transition hover:bg-white/70 dark:border-slate-700/40 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700/70"
            @click="isOpen = !isOpen"
            aria-label="Notifications"
        >
            <Bell class="h-5 w-5" />
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[11px] font-semibold text-white shadow"
            >
                {{ unreadCount }}
            </span>
        </button>

        <div
            v-if="isOpen"
            ref="dropdownRef"
            class="absolute right-0 z-40 mt-3 w-80 overflow-hidden rounded-xl border border-white/30 bg-white/85 shadow-xl backdrop-blur-lg transition dark:border-slate-700/50 dark:bg-slate-900/80"
        >
            <div class="flex items-center justify-between border-b border-white/30 bg-white/70 px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700/40 dark:bg-slate-900/60 dark:text-slate-100">
                <span>Notifications</span>
                <button
                    v-if="unreadCount > 0"
                    type="button"
                    class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-300 dark:hover:text-indigo-200"
                    @click="markAllRead"
                >
                    Mark all read
                </button>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <div
                    v-if="notifications.length > 0"
                    class="divide-y divide-white/20 dark:divide-slate-700/30"
                >
                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        type="button"
                        class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-white/60 dark:hover:bg-slate-800/60"
                        :class="{ 'opacity-70': notification.read_at }"
                        @click="handleNotificationClick(notification)"
                    >
                        <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20">
                            <component :is="resolveIcon(notification.type)" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ notification.data?.sender_name ?? notification.data?.title ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                {{ notification.data?.message_preview ?? notification.data?.body ?? 'Tap to view details.' }}
                            </p>
                        </div>
                    </button>
                </div>
                <div v-else class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    You're all caught up.
                </div>
            </div>

            <div class="border-t border-white/20 bg-white/60 px-4 py-2 text-right text-xs dark:border-slate-700/40 dark:bg-slate-900/60">
                <Link
                    href="/notifications"
                    class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-300 dark:hover:text-indigo-200"
                    @click="isOpen = false"
                >
                    View all notifications
                </Link>
            </div>
        </div>
    </div>
</template>
