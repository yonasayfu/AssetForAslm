<script setup>
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const notifications = ref([]);
const totalUnread = ref(0);
const showDropdown = ref(false);
const loading = ref(false);

const unreadCount = computed(() =>
    totalUnread.value ?? notifications.value.filter((notification) => !notification.read_at).length,
);

const normaliseNotification = (notification) => ({
    id: notification.id,
    data: notification.data ?? {},
    read_at: notification.read_at ?? null,
    created_at: notification.created_at ?? null,
});

const fetchNotifications = async () => {
    try {
        loading.value = true;
        const { data } = await axios.get('/notifications/unread');
        notifications.value = Array.isArray(data?.notifications)
            ? data.notifications.map(normaliseNotification)
            : [];
        totalUnread.value =
            typeof data?.unread_count === 'number'
                ? data.unread_count
                : notifications.value.filter((notification) => !notification.read_at).length;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (notificationId) => {
    try {
        await axios.post(`/notifications/${notificationId}/read`);
        await fetchNotifications();
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllRead = async () => {
    try {
        await axios.post('/notifications/read-all');
        await fetchNotifications();
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

const toggleDropdown = async () => {
    showDropdown.value = !showDropdown.value;

    if (showDropdown.value) {
        await fetchNotifications();
    }
};

const handleDocumentClick = (event) => {
    if (showDropdown.value && !event.target.closest('.notification-bell')) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    fetchNotifications();
    document.addEventListener('click', handleDocumentClick);
});

onUnmounted(() => {
    document.removeEventListener('click', handleDocumentClick);
});

const formattedTimestamp = (timestamp) => {
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
    <div class="relative notification-bell">
        <button
            type="button"
            class="relative rounded-full p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none dark:text-slate-300 dark:hover:bg-slate-800/70"
            @click="toggleDropdown"
        >
            <svg
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 inline-flex min-h-[1.25rem] min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-2 text-xs font-semibold text-white shadow-sm"
            >
                {{ unreadCount }}
            </span>
        </button>

        <div
            v-if="showDropdown"
            class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                Notifications
                <button
                    v-if="unreadCount > 0"
                    type="button"
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-300"
                    @click="markAllRead"
                >
                    Mark all as read
                </button>
            </div>
            <div class="max-h-96 divide-y divide-slate-200 dark:divide-slate-800">
                <div v-if="loading" class="px-4 py-3 text-sm text-slate-500 dark:text-slate-300">
                    Loading notifications…
                </div>
                <div
                    v-else-if="notifications.length === 0"
                    class="px-4 py-4 text-sm text-slate-500 dark:text-slate-400"
                >
                    You are all caught up. No new notifications.
                </div>
                <template v-else>
                    <Link
                        v-for="notification in notifications"
                        :key="notification.id"
                        :href="notification.data?.url ?? '#'"
                        class="block px-4 py-3 text-sm transition hover:bg-slate-50 dark:hover:bg-slate-800/70"
                        :class="{
                            'bg-indigo-50/80 dark:bg-indigo-500/10 font-semibold text-slate-900 dark:text-slate-100':
                                !notification.read_at,
                            'text-slate-600 dark:text-slate-300': notification.read_at,
                        }"
                        @click="
                            markAsRead(notification.id);
                            showDropdown = false;
                        "
                    >
                        <p>{{ notification.data?.message ?? 'Notification' }}</p>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">
                            {{ formattedTimestamp(notification.created_at) }}
                        </span>
                    </Link>
                </template>
            </div>
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-2 text-right dark:border-slate-700 dark:bg-slate-800">
                <Link
                    href="/notifications"
                    class="text-sm font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-300"
                    @click="showDropdown = false"
                >
                    View all notifications
                </Link>
            </div>
        </div>
    </div>
</template>
