<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import axios from 'axios';

const challengeType = ref('app_code'); // 'app_code', 'recovery_code', 'email_recovery_code'
const emailRecoveryCodeSent = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
    email_recovery_code: '',
});

const submit = () => {
    if (challengeType.value === 'email_recovery_code') {
        form.post(route('two-factor-email-recovery.verify'), {
            onFinish: () => form.reset('email_recovery_code'),
        });
    } else {
        form.post('/two-factor-challenge', {
            onFinish: () => form.reset('code', 'recovery_code'),
        });
    }
};

const sendEmailRecoveryCode = async () => {
    try {
        await axios.post(route('two-factor-email-recovery.send'));
        emailRecoveryCodeSent.value = true;
    } catch (error) {
        console.error('Error sending email recovery codes:', error);
    }
};

const showAppCodeInput = computed(() => challengeType.value === 'app_code');
const showRecoveryCodeInput = computed(() => challengeType.value === 'recovery_code');
const showEmailRecoveryCodeInput = computed(() => challengeType.value === 'email_recovery_code');
</script>
</script>

<template>
    <AuthLayout title="Two Factor Challenge" description="Please confirm access to your account by entering the authentication code provided by your authenticator application.">
        <Head title="Two Factor Challenge" />

        <div v-if="showAppCodeInput">
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
                    <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="challengeType = 'recovery_code'">
                        Use a recovery code
                    </button>
                    <button type="button" class="ml-4 text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="challengeType = 'email_recovery_code'">
                        Use email recovery
                    </button>

                    <Button class="ml-4" :disabled="form.processing">
                        Log in
                    </Button>
                </div>
            </form>
        </div>

        <div v-if="showRecoveryCodeInput">
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
                    <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="challengeType = 'app_code'">
                        Use an authentication code
                    </button>
                    <button type="button" class="ml-4 text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="challengeType = 'email_recovery_code'">
                        Use email recovery
                    </button>

                    <Button class="ml-4" :disabled="form.processing">
                        Log in
                    </Button>
                </div>
            </form>
        </div>

        <div v-if="showEmailRecoveryCodeInput">
            <div class="mt-4 text-sm text-gray-600">
                Please confirm access to your account by entering one of your email recovery codes.
            </div>

            <form @submit.prevent="submit">
                <div v-if="!emailRecoveryCodeSent">
                    <p class="text-sm text-gray-600 mb-4">Click the button below to send recovery codes to your recovery email address.</p>
                    <Button type="button" @click="sendEmailRecoveryCode" :disabled="form.processing">
                        Send Email Recovery Codes
                    </Button>
                </div>
                <div v-else>
                    <Label for="email_recovery_code">Email Recovery Code</Label>
                    <Input
                        id="email_recovery_code"
                        v-model="form.email_recovery_code"
                        type="text"
                        autocomplete="one-time-code"
                        class="block mt-1 w-full"
                    />
                    <InputError :message="form.errors.email_recovery_code" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="challengeType = 'app_code'">
                        Use an authentication code
                    </button>
                    <button type="button" class="ml-4 text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer" @click.prevent="challengeType = 'recovery_code'">
                        Use a recovery code
                    </button>

                    <Button class="ml-4" :disabled="form.processing || !emailRecoveryCodeSent">
                        Log in
                    </Button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>