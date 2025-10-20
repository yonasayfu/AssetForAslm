<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { Search, X, User, Users, Package } from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
    type Component,
} from 'vue';

type SearchResult = {
    type: string;
    category: string;
    title: string;
    description?: string | null;
    url: string;
    icon?: string | null;
};

const isOpen = ref(false);
const query = ref('');
const results = ref<SearchResult[]>([]);
const isLoading = ref(false);
const inputRef = ref<HTMLInputElement | null>(null);

const iconMap: Record<string, Component> = {
    user: User,
    users: Users,
    package: Package,
    default: Search,
};

const iconComponent = (icon?: string | null) => iconMap[icon ?? ''] ?? iconMap.default;

const groupedResults = computed(() => {
    const groups = new Map<string, SearchResult[]>();

    for (const result of results.value) {
        const key = result.category || 'Other';
        if (!groups.has(key)) {
            groups.set(key, []);
        }

        groups.get(key)!.push(result);
    }

    return Array.from(groups.entries()).map(([name, items]) => ({
        name,
        items,
    }));
});

const executeSearch = useDebounceFn(async () => {
    const term = query.value.trim();

    if (term.length < 2) {
        results.value = [];
        isLoading.value = false;
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.get('/global-search', {
            params: { q: term },
        });

        results.value = response.data ?? [];
    } catch (error) {
        console.error('Global search failed', error);
        results.value = [];
    } finally {
        isLoading.value = false;
    }
}, 250);

watch(query, () => executeSearch());

const open = async () => {
    if (isOpen.value) {
        return;
    }

    isOpen.value = true;
    await nextTick();
    inputRef.value?.focus();
    document.body.style.overflow = 'hidden';
};

const close = () => {
    if (!isOpen.value) {
        return;
    }

    isOpen.value = false;
    query.value = '';
    results.value = [];
    isLoading.value = false;
    document.body.style.overflow = '';
};

const navigate = (url: string) => {
    router.visit(url);
    close();
};

const handleBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        close();
    }
};

const handleShortcut = (event: KeyboardEvent) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        open();
    }
};

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        close();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleShortcut);
    window.addEventListener('keydown', handleEscape);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleShortcut);
    window.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="flex w-full max-w-md items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            @click="open"
        >
            <Search class="h-4 w-4" />
            <span class="hidden sm:inline">Search...</span>
            <kbd
                class="ml-auto hidden items-center gap-1 rounded border border-slate-200 bg-slate-100 px-1.5 font-mono text-[10px] font-medium text-slate-600 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 sm:inline-flex"
            >
                <span class="text-xs">⌘</span>K
            </kbd>
        </button>
    </div>

    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-[10000] flex items-start justify-center bg-black/40 backdrop-blur-sm pt-16 md:pt-24"
            @click="handleBackdropClick"
        >
            <div
                class="w-full max-w-2xl overflow-hidden rounded-xl border border-slate-200/70 bg-white shadow-2xl dark:border-slate-700/80 dark:bg-slate-900"
                @click.stop
            >
                <div class="flex items-center gap-3 border-b border-slate-200/70 bg-slate-50/60 px-4 py-3 dark:border-slate-700 dark:bg-slate-900/80">
                    <Search class="h-4 w-4 text-slate-400" />
                    <input
                        ref="inputRef"
                        v-model="query"
                        type="text"
                        placeholder="Search staff, users, assets..."
                        class="flex-1 border-none bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-slate-100"
                    />
                    <button
                        type="button"
                        class="rounded-md p-1 text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-200"
                        @click="close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <div
                        v-if="isLoading"
                        class="flex items-center justify-center px-4 py-6 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Searching...
                    </div>

                    <div
                        v-else-if="!query || query.length < 2"
                        class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400"
                    >
                        Type at least two characters to search.
                    </div>

                    <div
                        v-else-if="!results.length"
                        class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400"
                    >
                        No results found for "{{ query }}".
                    </div>

                    <div v-else class="py-2">
                        <div
                            v-for="group in groupedResults"
                            :key="group.name"
                            class="mb-4 last:mb-0"
                        >
                            <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ group.name }} ({{ group.items.length }})
                            </div>

                            <button
                                v-for="result in group.items"
                                :key="result.url"
                                type="button"
                                class="flex w-full items-start gap-3 px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                                @click="navigate(result.url)"
                            >
                                <component
                                    :is="iconComponent(result.icon)"
                                    class="h-4 w-4 text-slate-400"
                                />
                                <span class="flex-1">
                                    <span class="block font-medium text-slate-900 dark:text-slate-100">
                                        {{ result.title }}
                                    </span>
                                    <span
                                        v-if="result.description"
                                        class="block text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ result.description }}
                                    </span>
                                </span>
                                <span class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                    {{ result.type }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200/70 bg-slate-50/60 px-4 py-3 text-[11px] text-slate-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-400">
                    Press ⌘ + K / Ctrl + K to open · Esc to close
                </div>
            </div>
        </div>
    </Teleport>
</template>
