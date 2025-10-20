<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        as?: string;
        size?: 'md' | 'sm';
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        as: 'button',
        size: 'md',
        type: 'button',
    },
);

const tag = computed(() => props.as ?? 'button');
const sizeClass = computed(() =>
    props.size === 'sm' ? 'btn-glass-sm' : undefined,
);
const componentType = computed(() =>
    tag.value === 'button' ? props.type : undefined,
);
</script>

<template>
    <component
        :is="tag"
        class="btn-glass"
        :class="sizeClass"
        :type="componentType"
        v-bind="$attrs"
    >
        <slot />
    </component>
</template>
