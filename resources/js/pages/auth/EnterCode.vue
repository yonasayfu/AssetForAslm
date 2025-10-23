<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    status: {
        type: String,
        default: ''
    },
});

const form = useForm({
    email: props.email,
    code: '',
});

const submit = () => {
    form.post('/verify-code', {
        onFinish: () => form.reset('code'),
    });
};
</script>

<template>
    <Head title="Enter Verification Code" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">

            <div class="mb-4 text-sm text-gray-600">
                A 6-digit verification code has been sent to your recovery email address. Please enter it below.
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label for="code" class="block font-medium text-sm text-gray-700">Verification Code</label>
                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required
                        autofocus
                        autocomplete="one-time-code"
                    />
                    <div v-if="form.errors.code" class="mt-2 text-sm text-red-600">
                        {{ form.errors.code }}
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150" :disabled="form.processing">
                        Verify Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
