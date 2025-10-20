<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        as?: string;
        variant?: 'default' | 'lite';
        padding?: string;
        contentClass?: string;
        disableShine?: boolean;
    }>(),
    {
        as: 'div',
        variant: 'default',
        padding: 'p-6',
        contentClass: '',
        disableShine: false,
    },
);

const tag = computed(() => props.as ?? 'div');
const variantAttr = computed(() =>
    props.variant === 'default' ? undefined : props.variant,
);
const paddingClass = computed(() => props.padding ?? '');
const contentClass = computed(() => props.contentClass ?? '');
const showShine = computed(() => !props.disableShine);
</script>

<template>
    <component
        :is="tag"
        class="liquidGlass-wrapper"
        :class="paddingClass"
        :data-variant="variantAttr"
        v-bind="$attrs"
    >
        <span
            v-if="showShine"
            class="liquidGlass-inner-shine"
            aria-hidden="true"
        />
        <div class="liquidGlass-content" :class="contentClass">
            <slot />
        </div>
    </component>
</template>
