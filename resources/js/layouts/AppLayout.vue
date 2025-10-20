<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import Toast from '@/components/Toast.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { eventBus } from '@/lib/eventBus';
import type { BreadcrumbItemType } from '@/types';
import { onMounted, onUnmounted, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const confirmOpen = ref(false);
const confirmTitle = ref<string>('Are you sure?');
const confirmMessage = ref<string>('');
const confirmOkText = ref<string>('Confirm');
const confirmCancelText = ref<string>('Cancel');

let resolveConfirm: ((result: boolean) => void) | null = null;

const handleConfirmRequest = (payload: {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    __resolve: (result: boolean) => void;
}) => {
    confirmTitle.value = payload.title ?? 'Are you sure?';
    confirmMessage.value = payload.message ?? '';
    confirmOkText.value = payload.confirmText ?? 'Confirm';
    confirmCancelText.value = payload.cancelText ?? 'Cancel';
    resolveConfirm = payload.__resolve;
    confirmOpen.value = true;
};

const closeConfirm = (accepted: boolean) => {
    confirmOpen.value = false;
    resolveConfirm?.(accepted);
    resolveConfirm = null;
};

onMounted(() => {
    eventBus.on('confirm:open', handleConfirmRequest);
});

onUnmounted(() => {
    eventBus.off('confirm:open', handleConfirmRequest);
});
</script>

<template>
    <AppLayout :breadcrumbs="props.breadcrumbs">
        <Toast />
        <slot />
        <ConfirmModal
            :open="confirmOpen"
            :title="confirmTitle"
            :message="confirmMessage"
            :confirm-text="confirmOkText"
            :cancel-text="confirmCancelText"
            @update:open="(value) => {
                if (!value && confirmOpen) {
                    closeConfirm(false);
                }
            }"
            @confirm="() => closeConfirm(true)"
            @cancel="() => closeConfirm(false)"
        />
    </AppLayout>
</template>
