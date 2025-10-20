import { eventBus } from '@/lib/eventBus';

export type ConfirmOptions = {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
};

export function confirmDialog(options: ConfirmOptions): Promise<boolean> {
    return new Promise((resolve) => {
        eventBus.emit('confirm:open', {
            ...options,
            __resolve: (result: boolean) => resolve(result),
        });
    });
}
