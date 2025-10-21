<script setup lang="ts">
import GlassButton from '@/components/GlassButton.vue';
import { Link } from '@inertiajs/vue3';
import { Download, Printer, Plus } from 'lucide-vue-next';

interface Props {
    title: string;
    description?: string;
    createRoute?: string;
    createText?: string;
    showCreate?: boolean;
    showExport?: boolean;
    showPrint?: boolean;
    customActions?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    createText: 'Add New',
    showCreate: true,
    showExport: true,
    showPrint: true,
    customActions: false,
});

const emit = defineEmits<{
    export: [type: string];
    print: [];
}>();

const handleExport = (type: string) => {
    emit('export', type);
};

const handlePrint = () => {
    emit('print');
};
</script>

<template>
    <div class="liquidGlass-wrapper print:hidden">
        <span class="liquidGlass-inner-shine" aria-hidden="true" />
        <div class="liquidGlass-content flex flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm text-slate-600 dark:text-slate-300">
                    {{ description }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <slot name="actions" v-if="customActions" />

                <GlassButton
                    v-if="showCreate && createRoute"
                    as="span"
                    size="sm"
                    variant="primary"
                >
                    <Link :href="createRoute" class="flex items-center gap-2">
                        <Plus class="size-4" />
                        <span>{{ createText }}</span>
                    </Link>
                </GlassButton>

                <GlassButton
                    v-if="showExport"
                    as="button"
                    size="sm"
                    type="button"
                    class="flex items-center gap-2"
                    variant="success"
                    @click="handleExport('csv')"
                >
                    <Download class="size-4" />
                    <span class="hidden sm:inline">Export CSV</span>
                </GlassButton>

                <GlassButton
                    v-if="showPrint"
                    as="button"
                    size="sm"
                    type="button"
                    class="flex items-center gap-2"
                    variant="warning"
                    @click="handlePrint"
                >
                    <Printer class="size-4" />
                    <span class="hidden sm:inline">Print Current</span>
                </GlassButton>
            </div>
        </div>
    </div>
</template>
