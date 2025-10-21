<script setup lang="ts">
import { onBeforeUnmount, ref, watch, computed } from 'vue';

interface Props {
    label: string;
    hint?: string;
    accept?: string;
    modelValue: File | null;
    existingUrl?: string | null;
    existingLabel?: string | null;
    variant?: 'image' | 'document';
}

const props = withDefaults(defineProps<Props>(), {
    hint: '',
    accept: 'image/*,.pdf,.doc,.docx',
    existingUrl: null,
    existingLabel: null,
    variant: 'document',
});

const emit = defineEmits<{
    'update:modelValue': [File | null];
    'clear-existing': [];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const dragging = ref(false);
const localPreviewUrl = ref<string | null>(null);
const showPreview = ref(false);

const revokePreview = () => {
    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value);
        localPreviewUrl.value = null;
    }
};

const previewUrl = computed(() => {
    return localPreviewUrl.value ?? props.existingUrl ?? null;
});

const fileLabel = computed(() => {
    if (props.modelValue) {
        return props.modelValue.name;
    }

    return props.existingLabel ?? null;
});

const isImageVariant = computed(() => props.variant === 'image');

watch(
    () => props.modelValue,
    (file) => {
        revokePreview();

        if (file && isImageVariant.value) {
            localPreviewUrl.value = URL.createObjectURL(file);
        }
    }
);

onBeforeUnmount(() => {
    revokePreview();
});

const handleBrowse = () => {
    fileInput.value?.click();
};

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    emit('update:modelValue', file);

};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    dragging.value = false;

    const file = event.dataTransfer?.files?.[0] ?? null;
    emit('update:modelValue', file ?? null);
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    dragging.value = true;
};

const handleDragLeave = () => {
    dragging.value = false;
};

const clearFile = () => {
    const clearingExistingFile = !props.modelValue && !!props.existingUrl;

    if (fileInput.value) {
        fileInput.value.value = '';
    }

    revokePreview();
    emit('update:modelValue', null);

    if (clearingExistingFile) {
        emit('clear-existing');
    }
};

const openPreview = () => {
    if (!previewUrl.value) {
        return;
    }

    if (isImageVariant.value) {
        showPreview.value = true;
    } else {
        window.open(previewUrl.value, '_blank');
    }
};

const closePreview = () => {
    showPreview.value = false;
};
</script>

<template>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ label }}
        </label>

        <div
            class="relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white/70 px-5 py-8 text-center transition hover:border-indigo-400 hover:bg-indigo-50/60 dark:border-slate-700 dark:bg-slate-900/40 dark:hover:border-indigo-300/70 dark:hover:bg-slate-900/60"
            :class="dragging ? 'border-indigo-500 bg-indigo-50/80 dark:border-indigo-300/80' : ''"
            @click.prevent="handleBrowse"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
        >
            <input
                ref="fileInput"
                type="file"
                class="absolute inset-0 cursor-pointer opacity-0"
                :accept="accept"
                @change="handleFileChange"
            />

            <div class="flex flex-col items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                    {{ variant === 'image' ? 'PNG, JPG up to 5MB' : 'PDF or DOC up to 10MB' }}
                </span>
                <p class="font-medium">
                    Drag & drop a file, or <span class="text-indigo-600">browse</span>
                </p>
                <p class="text-xs text-slate-500">
                    {{ hint || 'Files are stored under module specific folders in storage/app/public.' }}
                </p>
            </div>
        </div>

        <div v-if="fileLabel" class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white py-3 px-4 text-sm text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
            <div class="flex flex-col">
                <span class="font-medium text-slate-800 dark:text-slate-100">{{ fileLabel }}</span>
                <span v-if="modelValue" class="text-xs text-slate-500 dark:text-slate-400">
                    Selected file is not yet uploaded.
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    v-if="previewUrl"
                    type="button"
                    class="rounded-md border border-indigo-200 px-3 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-400/40 dark:text-indigo-200 dark:hover:bg-indigo-500/10"
                    @click.stop="openPreview"
                >
                    Preview
                </button>
                <button
                    type="button"
                    class="rounded-md border border-transparent bg-red-500/10 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-500/20 dark:bg-red-500/20 dark:text-red-200"
                    @click.stop="clearFile"
                >
                    Remove
                </button>
            </div>
        </div>

        <div
            v-if="showPreview && previewUrl"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-6"
        >
            <button
                type="button"
                class="absolute right-6 top-6 rounded-full bg-white/80 px-3 py-1 text-sm font-medium text-slate-700 shadow hover:bg-white"
                @click="closePreview"
            >
                Close
            </button>
            <img
                :src="previewUrl"
                alt="Preview"
                class="max-h-[80vh] w-full max-w-3xl rounded-lg border border-white/30 bg-white object-contain shadow-2xl"
            />
        </div>
    </div>
</template>
