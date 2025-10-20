<script setup lang="ts">
import { computed } from 'vue';
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import AuthGlassLayout from '@/layouts/auth/AuthGlassLayout.vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        variant?: 'glass' | 'simple' | 'card' | 'split';
    }>(),
    {
        variant: 'glass',
    },
);

const ResolvedLayout = computed(() => {
    switch (props.variant) {
        case 'simple':
            return AuthSimpleLayout;
        case 'card':
            return AuthCardLayout;
        case 'split':
            return AuthSplitLayout;
        default:
            return AuthGlassLayout;
    }
});
</script>

<template>
    <component :is="ResolvedLayout" :title="title" :description="description">
        <slot />
    </component>
</template>
