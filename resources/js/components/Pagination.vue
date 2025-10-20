<script setup lang="ts">
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();

const changePage = (url: string | null) => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <nav v-if="links?.length" class="flex items-center gap-1">
        <button
            v-for="link in links"
            :key="link.label"
            type="button"
            class="rounded-md px-3 py-1 text-sm transition"
            :class="[
                link.active
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : link.url
                        ? 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'
                        : 'cursor-not-allowed text-slate-400 dark:text-slate-500'
            ]"
            :disabled="!link.url || link.active"
            v-html="link.label"
            @click="changePage(link.url)"
        />
    </nav>
</template>
