<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        as?: string;
        size?: 'md' | 'sm';
        type?: 'button' | 'submit' | 'reset';
        variant?: 'default' | 'primary' | 'secondary' | 'success' | 'warning' | 'danger';
    }>(),
    {
        as: 'button',
        size: 'md',
        type: 'button',
        variant: 'default',
    },
);

const tag = computed(() => props.as ?? 'button');
const sizeClass = computed(() =>
    props.size === 'sm' ? 'btn-glass-sm' : undefined,
);

const variantClass = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'btn-variant-primary';
        case 'secondary':
            return 'btn-variant-secondary';
        case 'success':
            return 'btn-variant-success';
        case 'warning':
            return 'btn-variant-warning';
        case 'danger':
            return 'btn-variant-danger';
        default:
            return undefined;
    }
});

const componentType = computed(() =>
    tag.value === 'button' ? props.type : undefined,
);
</script>

<template>
    <component
        :is="tag"
        class="btn-glass"
        :class="[sizeClass, variantClass]"
        :type="componentType"
        v-bind="$attrs"
    >
        <slot />
    </component>
</template>
