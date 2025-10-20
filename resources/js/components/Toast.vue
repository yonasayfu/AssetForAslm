<script setup lang="ts">
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

type FlashPayload = {
    banner?: string;
    bannerStyle?: 'success' | 'danger' | 'warning' | 'info';
};

const page = usePage<{ flash?: FlashPayload }>();

const show = ref(false);
const hideTimer = ref<number | null>(null);

const style = computed<FlashPayload['bannerStyle']>(() => page.props.flash?.bannerStyle ?? 'success');
const message = computed(() => page.props.flash?.banner ?? '');

const clearTimer = () => {
    if (hideTimer.value) {
        clearTimeout(hideTimer.value);
        hideTimer.value = null;
    }
};

const autoHideAfter = computed(() => {
    switch (style.value) {
        case 'danger':
        case 'warning':
            return 12000;
        case 'info':
            return 9000;
        default:
            return 6000;
    }
});

const startTimer = () => {
    clearTimer();
    if (!message.value) {
        show.value = false;
        return;
    }

    show.value = true;
    hideTimer.value = window.setTimeout(() => {
        show.value = false;
        hideTimer.value = null;
    }, autoHideAfter.value);
};

const close = () => {
    show.value = false;
    clearTimer();
};

const pause = () => {
    clearTimer();
};

const resume = () => {
    if (!show.value || !message.value) return;
    hideTimer.value = window.setTimeout(() => {
        show.value = false;
        hideTimer.value = null;
    }, 3000);
};

onMounted(startTimer);

watch(
    () => page.props.flash,
    () => startTimer(),
    { deep: true },
);

const styleClasses = computed(() => {
    switch (style.value) {
        case 'danger':
            return 'border-red-200/60 text-red-900 dark:text-red-100 bg-red-50/90 dark:bg-red-500/30';
        case 'warning':
            return 'border-amber-200/60 text-amber-900 dark:text-amber-100 bg-amber-50/90 dark:bg-amber-500/30';
        case 'info':
            return 'border-sky-200/60 text-sky-900 dark:text-sky-100 bg-sky-50/90 dark:bg-sky-500/30';
        default:
            return 'border-emerald-200/60 text-emerald-900 dark:text-emerald-100 bg-emerald-50/90 dark:bg-emerald-500/30';
    }
});
</script>

<template>
    <transition name="fade">
        <div
            v-if="show && message"
            class="pointer-events-auto fixed inset-x-0 bottom-6 z-[120] flex justify-center px-4 sm:inset-auto sm:right-10 sm:top-8 sm:justify-end"
        >
            <GlassCard
                padding="p-0"
                content-class=""
                class="w-full max-w-md border backdrop-blur-xl sm:w-96"
                :class="styleClasses"
                @mouseenter="pause"
                @mouseleave="resume"
            >
                <div class="flex items-start gap-3 px-5 py-4">
                    <div class="flex-1 text-sm font-medium leading-5">
                        {{ message }}
                    </div>
                    <GlassButton
                        size="sm"
                        class="!py-1 text-xs font-semibold uppercase tracking-wide"
                        @click="close"
                    >
                        Dismiss
                    </GlassButton>
                </div>
            </GlassCard>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
