<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import GlassButton from '@/components/GlassButton.vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        message?: string;
        confirmText?: string;
        cancelText?: string;
    }>(),
    {
        title: 'Are you sure?',
        message: '',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
    },
);

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

const close = () => emit('update:open', false);
const handleCancel = () => {
    emit('cancel');
    close();
};
const handleConfirm = () => {
    emit('confirm');
    close();
};
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ props.title }}</DialogTitle>
                <DialogDescription v-if="props.message">
                    {{ props.message }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <GlassButton
                    size="sm"
                    class="justify-center bg-transparent text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white"
                    @click="handleCancel"
                >
                    {{ props.cancelText }}
                </GlassButton>
                <GlassButton
                    size="sm"
                    class="justify-center bg-emerald-500/80 text-emerald-950 hover:bg-emerald-500 dark:bg-emerald-500/40 dark:text-emerald-100"
                    @click="handleConfirm"
                >
                    {{ props.confirmText }}
                </GlassButton>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
