<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'; // Assuming these components exist
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios'; // Assuming axios is available globally or imported

const page = usePage();
const user = computed(() => page.props.auth.user);

const enablingTwoFactorAuthentication = ref(false);
const confirmingTwoFactorAuthentication = ref(false);
const twoFactorRecoveryCodes = ref([]);
const setupKey = ref(null); // To store the setup key for QR code generation

const confirmationForm = useForm({
    code: '',
});

const enableTwoFactorAuthentication = () => {
    enablingTwoFactorAuthentication.value = true;
    useForm({}).post('/user/two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            // Force a full page visit to ensure the user prop is completely refreshed
            router.visit(window.location.href, { preserveScroll: true, preserveState: false });
        },
        onFinish: () => {
            enablingTwoFactorAuthentication.value = false;
        },
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post('/user/confirm-two-factor-authentication', {
        errorBag: 'confirmTwoFactorAuthentication',
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirmingTwoFactorAuthentication.value = false;
            confirmationForm.reset();
        },
    });
};

const disableTwoFactorAuthentication = () => {
    useForm({}).delete('/user/two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            confirmingTwoFactorAuthentication.value = false;
            twoFactorRecoveryCodes.value = [];
            setupKey.value = null;
        },
    });
};

const showQrCode = () => {
    axios.get('/user/two-factor-qr-code').then(response => {
        setupKey.value = response.data.svg;
    });
};

const showRecoveryCodes = () => {
    axios.get('/user/two-factor-recovery-codes').then(response => {
        twoFactorRecoveryCodes.value = response.data;
    });
};

const regenerateRecoveryCodes = () => {
    axios.post('/user/two-factor-recovery-codes').then(response => {
        showRecoveryCodes();
    });
};

const twoFactorEnabled = computed(() => !enablingTwoFactorAuthentication.value && !!user.value?.two_factor_secret);

watch(twoFactorEnabled, (enabled) => {
    if (enabled) {
        showQrCode();
        showRecoveryCodes();
    }
}, { immediate: true });
</script>

<template>
    <div>
        <h2 class="text-lg font-medium text-gray-900">Two Factor Authentication</h2>

        <div class="mt-3 max-w-xl text-sm text-gray-600">
            <p>
                Add additional security to your account using two factor authentication.
            </p>
        </div>

        <h3 v-if="twoFactorEnabled && !confirmingTwoFactorAuthentication" class="text-lg font-medium text-gray-900">
            You have enabled two factor authentication.
        </h3>

        <h3 v-if="twoFactorEnabled && confirmingTwoFactorAuthentication" class="text-lg font-medium text-gray-900">
            Finish enabling two factor authentication.
        </h3>

        <h3 v-if="!twoFactorEnabled" class="text-lg font-medium text-gray-900">
            You have not enabled two factor authentication.
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-600">
            <p v-if="twoFactorEnabled && !confirmingTwoFactorAuthentication">
                Two factor authentication is now enabled. Scan the following QR code using your phone's authenticator application.
            </p>
            <p v-if="twoFactorEnabled && confirmingTwoFactorAuthentication">
                To finish enabling two factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.
            </p>
            <p v-if="!twoFactorEnabled">
                When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
            </p>
        </div>

        <div v-if="twoFactorEnabled">
            <div v-if="setupKey" class="mt-4">
                <div v-html="setupKey"></div>
            </div>

            <div v-if="twoFactorRecoveryCodes.length > 0" class="mt-4">
                <p class="font-semibold">
                    Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.
                </p>

                <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                    <div v-for="code in twoFactorRecoveryCodes" :key="code">
                        {{ code }}
                    </div>
                </div>
            </div>

            <div v-if="confirmingTwoFactorAuthentication" class="mt-4">
                <Label for="code">Code</Label>
                <Input
                    id="code"
                    v-model="confirmationForm.code"
                    type="text"
                    name="code"
                    class="block mt-1 w-1/2"
                    inputmode="numeric"
                    autofocus
                    autocomplete="one-time-code"
                    @keyup.enter="confirmTwoFactorAuthentication"
                />
                <InputError :message="confirmationForm.errors.code" class="mt-2" />
            </div>
        </div>

        <div class="mt-5">
            <div v-if="!twoFactorEnabled">
                <Button :disabled="enablingTwoFactorAuthentication" @click="enableTwoFactorAuthentication">
                    Enable
                </Button>
            </div>

            <div v-else>
                <Button v-if="confirmingTwoFactorAuthentication" :disabled="confirmationForm.processing" @click="confirmTwoFactorAuthentication">
                    Confirm
                </Button>

                <Button v-if="twoFactorRecoveryCodes.length > 0 && !confirmingTwoFactorAuthentication" @click="regenerateRecoveryCodes" class="ml-3">
                    Regenerate Recovery Codes
                </Button>

                <Button v-if="!confirmingTwoFactorAuthentication" :disabled="enablingTwoFactorAuthentication" @click="disableTwoFactorAuthentication" class="ml-3">
                    Disable
                </Button>
            </div>
        </div>
    </div>
</template>
