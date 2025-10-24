<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

type AppearanceOption = {
    label: string;
    value: 'light' | 'dark' | 'system';
    icon: typeof Sun;
};

const { appearance, updateAppearance } = useAppearance();

const options: AppearanceOption[] = [
    { label: 'Light', value: 'light', icon: Sun },
    { label: 'Dark', value: 'dark', icon: Moon },
    { label: 'System', value: 'system', icon: Monitor },
];

const currentIcon = computed(() => {
    const match = options.find((option) => option.value === appearance.value);
    return match ? match.icon : Monitor;
});

const handleSelect = (value: AppearanceOption['value']) => {
    updateAppearance(value);
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="rounded-full border border-transparent transition hover:border-slate-200 dark:hover:border-slate-700"
            >
                <component :is="currentIcon" class="h-4 w-4" />
                <span class="sr-only">Toggle theme</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuItem
                v-for="option in options"
                :key="option.value"
                class="flex items-center gap-2"
                @click="handleSelect(option.value)"
            >
                <component :is="option.icon" class="h-4 w-4" />
                <span class="text-sm">{{ option.label }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
