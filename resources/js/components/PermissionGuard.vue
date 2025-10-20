<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    any?: string[];
    every?: string[];
}>();

const page = usePage<{
    auth: {
        permissions: string[];
    };
}>();

const permissionSet = computed(() => new Set(page.props.auth?.permissions ?? []));

const canRender = computed(() => {
    const permissions = permissionSet.value;

    if (props.every && props.every.length > 0) {
        return props.every.every((permission) => permissions.has(permission));
    }

    if (props.any && props.any.length > 0) {
        return props.any.some((permission) => permissions.has(permission));
    }

    return true;
});
</script>

<template>
    <slot v-if="canRender" />
</template>
