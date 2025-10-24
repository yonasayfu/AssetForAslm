<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios'; // Assuming axios is available globally or imported

const notifications = ref([]);
const showDropdown = ref(false);

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications/unread');
        notifications.value = Array.isArray(response.data) ? response.data : [];
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

const markAsRead = async (notificationId) => {
    try {
        await axios.post(`/notifications/${notificationId}/read`);
        await fetchNotifications(); // Refresh notifications
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllRead = async () => {
    try {
        await axios.post('/notifications/read-all');
        await fetchNotifications(); // Refresh notifications
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }d
};

onMounted(() => {
    fetchNotifications();
    // Optional: Poll for new notifications or use WebSockets
    // setInterval(fetchNotifications, 30000); // Example polling
});

// Close dropdown when clicking outside
const onClickOutside = (event) => {
    if (showDropdown.value && !event.target.closest('.notification-bell')) {
        showDropdown.value = false;
    }
};
// Add and remove event listener manually
onMounted(() => document.addEventListener('click', onClickOutside));
// onUnmounted(() => document.removeEventListener('click', onClickOutside)); // Not needed if component is always mounted
</script>

<template>
    <div class="relative notification-bell">
        <button @click="showDropdown = !showDropdown" class="relative p-2 text-gray-600 hover:text-gray-800 focus:outline-none">
            <!-- Bell Icon (replace with actual icon) -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span v-if="unreadCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">{{ unreadCount }}</span>
        </button>

        <div v-if="showDropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-10">
            <div class="py-1">
                <div v-if="notifications.length === 0" class="px-4 py-2 text-sm text-gray-500">No new notifications.</div>
                <template v-else>
                    <Link v-for="notification in notifications.slice(0, 5)" :key="notification.id" :href="notification.data.url || '#'" @click="markAsRead(notification.id); showDropdown = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" :class="{ 'font-bold bg-blue-50': !notification.read_at }">
                        {{ notification.data.message }}
                        <span class="block text-xs text-gray-500">{{ new Date(notification.created_at).toLocaleString() }}</span>
                    </Link>
                    <div class="border-t border-gray-200 mt-1"></div>
                    <Link href="/notifications" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-gray-100">View All Notifications</Link>
                    <button v-if="unreadCount > 0" @click="markAllRead(); showDropdown = false" class="block w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-100">Mark All As Read</button>
                </template>
            </div>
        </div>
    </div>
</template>