<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    notifications: Object, // Paginated notifications
});

const markAsRead = async (notificationId) => {
    try {
        await axios.post(route('notifications.markAsRead', notificationId));
        router.reload({ only: ['notifications'] }); // Refresh notifications
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.markAllRead'));
        router.reload({ only: ['notifications'] }); // Refresh notifications
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

const unreadNotifications = computed(() => props.notifications.data.filter(n => !n.read_at));
</script>

<template>
    <Head title="Notifications" />

    <!-- Assuming a layout is applied externally -->
    <div class="p-6 md:p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Notifications</h1>
            <button v-if="unreadNotifications.length > 0" @click="markAllAsRead" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Mark All As Read</button>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div v-if="notifications.data.length === 0" class="p-6 text-gray-500">
                You don't have any notifications yet.
            </div>
            <div v-else>
                <div v-for="notification in notifications.data" :key="notification.id" class="border-b last:border-b-0 p-4 hover:bg-gray-50" :class="{ 'bg-blue-50': !notification.read_at }">
                    <div class="flex justify-between items-center">
                        <Link :href="notification.data.url || '#'" @click="!notification.read_at && markAsRead(notification.id)" class="flex-grow">
                            <p class="font-semibold" :class="{ 'text-gray-800': !notification.read_at, 'text-gray-600': notification.read_at }">
                                {{ notification.data.message }}
                            </p>
                            <span class="text-xs text-gray-500">{{ new Date(notification.created_at).toLocaleString() }}</span>
                        </Link>
                        <button v-if="!notification.read_at" @click="markAsRead(notification.id)" class="ml-4 px-3 py-1 text-sm bg-gray-200 rounded-full hover:bg-gray-300">Mark as Read</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="notifications.links.length > 3" class="mt-6 flex justify-center">
            <div class="flex flex-wrap -mb-1">
                <template v-for="(link, key) in notifications.links">
                    <div v-if="link.url === null" :key="key" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                    <Link v-else :key="`link-${key}`" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-white': link.active }" :href="link.url" v-html="link.label" />
                </template>
            </div>
        </div>
    </div>
</template>