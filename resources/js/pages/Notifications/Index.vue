<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed } from 'vue';

interface NotificationPayload {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    notifications: {
        data: NotificationPayload[];
        links: PaginationLink[];
    };
}>();

const markAsRead = async (notificationId: string) => {
    try {
        await axios.post(`/notifications/${notificationId}/read`);
        router.reload({ only: ['notifications'] });
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/read-all');
        router.reload({ only: ['notifications'] });
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

const unreadNotifications = computed(() => props.notifications.data.filter((notification) => !notification.read_at));

const formatTimestamp = (timestamp: string | null) => {
    if (!timestamp) {
        return '';
    }

    const date = new Date(timestamp);

    if (Number.isNaN(date.getTime())) {
        return timestamp;
    }

    return date.toLocaleString();
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Notifications', href: '/notifications' }]">
        <Head title="Notifications" />

        <div class="flex items-center justify-between rounded-3xl border border-slate-200/70 bg-white/80 px-6 py-5 shadow-lg dark:border-slate-800/70 dark:bg-slate-900/60">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Notifications</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Stay up to date with system events and assignment updates.
                </p>
            </div>
            <button
                v-if="unreadNotifications.length > 0"
                type="button"
                class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                @click="markAllAsRead"
            >
                Mark all as read
            </button>
        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-xl dark:border-slate-800/70 dark:bg-slate-950/60">
            <div v-if="notifications.data.length === 0" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                You don't have any notifications yet.
            </div>
            <ul v-else class="divide-y divide-slate-200 dark:divide-slate-800">
                <li
                    v-for="notification in notifications.data"
                    :key="notification.id"
                    :class="{
                        'bg-indigo-50/60 dark:bg-indigo-500/10': !notification.read_at,
                        'bg-white/40 dark:bg-transparent': notification.read_at,
                    }"
                    class="group transition hover:bg-slate-50 dark:hover:bg-slate-900/80"
                >
                    <div class="flex flex-col gap-2 px-6 py-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex-1">
                            <Link
                                :href="(notification.data?.url as string) || '#'"
                                class="inline-flex items-start gap-2 text-left"
                                @click="() => {
                                    if (!notification.read_at) {
                                        markAsRead(notification.id);
                                    }
                                }"
                            >
                                <span
                                    class="mt-1 h-2 w-2 rounded-full"
                                    :class="notification.read_at ? 'bg-slate-300 dark:bg-slate-600' : 'bg-indigo-500 dark:bg-indigo-400'"
                                />
                                <div>
                                    <p
                                        class="text-sm font-medium"
                                        :class="notification.read_at ? 'text-slate-600 dark:text-slate-300' : 'text-slate-900 dark:text-slate-100'"
                                    >
                                        {{ (notification.data?.message as string) ?? 'Notification' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ formatTimestamp(notification.created_at) }}
                                    </p>
                                </div>
                            </Link>
                        </div>
                        <button
                            v-if="!notification.read_at"
                            type="button"
                            class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="markAsRead(notification.id)"
                        >
                            Mark as read
                        </button>
                    </div>
                </li>
            </ul>
        </div>

        <div v-if="notifications.links.length > 3" class="mt-6 flex justify-center">
            <nav class="flex flex-wrap gap-2 text-sm">
                <template v-for="(link, index) in notifications.links" :key="`pagination-${index}`">
                    <span
                        v-if="link.url === null"
                        v-html="link.label"
                        class="rounded-md border border-transparent px-4 py-2 text-slate-400"
                    />
                    <Link
                        v-else
                        :href="link.url"
                        v-html="link.label"
                        class="rounded-md border border-slate-200 px-4 py-2 transition hover:border-indigo-500 hover:text-indigo-600 focus:border-indigo-500 focus:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:text-indigo-300"
                        :class="{ 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300': link.active }"
                    />
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
