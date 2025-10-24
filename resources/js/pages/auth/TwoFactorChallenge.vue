<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const submit = () => {
    form.post('/two-factor-challenge', {
        onFinish: () => form.reset('code', 'recovery_code'),
    });
};
</script>

<template>
    <AuthLayout title="Two Factor Challenge" description="Please confirm access to your account by entering the authentication code provided by your authenticator application.">
        <Head title="Two Factor Challenge" />

        <div v-if="! recovery">
            <div class="mt-4 text-sm text-gray-600">
                Please confirm access to your account by entering the authentication code provided by your authenticator application.
            </div>

            <form @submit.prevent="submit">
                <div>
                    <Label for="code">Code</Label>
                    <Input
                        id="code"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        autofocus
                        autocomplete="one-time-code"
                        class="block mt-1 w-full"
                    />
                    <InputError :message="form.errors.code" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="recovery = true">
                        Use a recovery code
                    </button>

                    <Button class="ml-4" :disabled="form.processing">
                        Log in
                    </Button>
                </div>
            </form>
        </div>

        <div v-else>
            <div class="mt-4 text-sm text-gray-600">
                Please confirm access to your account by entering one of your emergency recovery codes.
            </div>

            <form @submit.prevent="submit">
                <div>
                    <Label for="recovery_code">Recovery Code</Label>
                    <Input
                        id="recovery_code"
                        v-model="form.recovery_code"
                        type="text"
                        autocomplete="one-time-code"
                        class="block mt-1 w-full"
                    />
                    <InputError :message="form.errors.recovery_code" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="recovery = false">
                        Use an authentication code
                    </button>

                    <Button class="ml-4" :disabled="form.processing">
                        Log in
                    </Button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>