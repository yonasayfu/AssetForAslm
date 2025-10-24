<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    preferences: {
        type: Array,
        default: () => [],
    },
    availableNotificationTypes: {
        type: Array,
        default: () => [],
    },
});

const localPreferences = ref([]);

onMounted(() => {
    // Initialize local preferences from props
    localPreferences.value = props.availableNotificationTypes.map(type => {
        return {
            type: type,
            channels: ['mail', 'database'].map(channel => { // Assuming mail and database are always available channels
                const existingPref = props.preferences.find(p => p.notification_type === type && p.channel === channel);
                return {
                    channel: channel,
                    enabled: existingPref ? existingPref.enabled : true, // Default to true if no preference exists
                };
            }),
        };
    });
});

const updatePreference = async (type, channel, enabled) => {
    try {
        await axios.post(route('profile.notification-preferences.update'), {
            notification_type: type,
            channel: channel,
            enabled: enabled,
        });
        // Optionally, refresh local preferences or show a success message
    } catch (error) {
        console.error('Error updating preference:', error);
    }
};
</script>

<template>
    <Head title="Notification Preferences" />

    <!-- Assuming a layout is applied externally -->
    <div class="p-6 md:p-10">
        <h2 class="text-lg font-medium text-gray-900">Notification Preferences</h2>

        <p class="mt-1 text-sm text-gray-600">
            Manage how you receive notifications from the application.
        </p>

        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <div v-if="localPreferences.length === 0" class="text-gray-500">
                No notification types available to manage.
            </div>
            <div v-else>
                <div v-for="prefType in localPreferences" :key="prefType.type" class="mb-6 pb-4 border-b last:border-b-0">
                    <h3 class="font-semibold text-gray-800 mb-2">{{ prefType.type.replace(/([A-Z])/g, ' $1').trim() }}</h3>
                    <div class="flex flex-wrap gap-4">
                        <div v-for="prefChannel in prefType.channels" :key="prefChannel.channel" class="flex items-center">
                            <input
                                :id="`${prefType.type}-${prefChannel.channel}`"
                                type="checkbox"
                                v-model="prefChannel.enabled"
                                @change="updatePreference(prefType.type, prefChannel.channel, prefChannel.enabled)"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <label :for="`${prefType.type}-${prefChannel.channel}`" class="ml-2 text-sm text-gray-700 capitalize">
                                {{ prefChannel.channel }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
