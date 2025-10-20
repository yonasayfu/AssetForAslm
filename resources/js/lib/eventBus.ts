import mitt from 'mitt';

type ApplicationEvents = {
    'confirm:open': {
        title?: string;
        message: string;
        confirmText?: string;
        cancelText?: string;
        __resolve: (result: boolean) => void;
    };
};

export const eventBus = mitt<ApplicationEvents>();
