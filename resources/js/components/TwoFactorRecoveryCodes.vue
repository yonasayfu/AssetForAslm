<script setup lang="ts">
import AlertError from '@/components/AlertError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { regenerateRecoveryCodes } from '@/routes/two-factor';
import { Form } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole, RefreshCw, Mail } from 'lucide-vue-next';
import { nextTick, onMounted, ref } from 'vue';
import axios from 'axios';

const { recoveryCodesList, fetchRecoveryCodes, errors, emailRecoveryCodesList, fetchEmailRecoveryCodes } = useTwoFactorAuth();
const isRecoveryCodesVisible = ref<boolean>(false);
const isEmailRecoveryCodesVisible = ref<boolean>(false);
const recoveryCodeSectionRef = ref<HTMLDivElement | null>(null);
const emailRecoveryCodeSectionRef = ref<HTMLDivElement | null>(null);

const toggleRecoveryCodesVisibility = async () => {
    if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }

    isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

    if (isRecoveryCodesVisible.value) {
        await nextTick();
        recoveryCodeSectionRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
};

const toggleEmailRecoveryCodesVisibility = async () => {
    if (!isEmailRecoveryCodesVisible.value && !emailRecoveryCodesList.value.length) {
        await fetchEmailRecoveryCodes();
    }

    isEmailRecoveryCodesVisible.value = !isEmailRecoveryCodesVisible.value;

    if (isEmailRecoveryCodesVisible.value) {
        await nextTick();
        emailRecoveryCodeSectionRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
};

const sendEmailRecoveryCodes = async () => {
    try {
        await axios.post(route('two-factor-email-recovery.send'));
        // Optionally, show a success message
    } catch (error) {
        console.error('Error sending email recovery codes:', error);
    }
};

onMounted(async () => {
    if (!recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }
    if (!emailRecoveryCodesList.value.length) {
        await fetchEmailRecoveryCodes();
    }
});
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle class="flex gap-3">
                <LockKeyhole class="size-4" />2FA Recovery Codes
            </CardTitle>
            <CardDescription>
                Recovery codes let you regain access if you lose your 2FA
                device. Store them in a secure password manager.
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div
                class="flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between"
            >
                <Button @click="toggleRecoveryCodesVisibility" class="w-fit">
                    <component
                        :is="isRecoveryCodesVisible ? EyeOff : Eye"
                        class="size-4"
                    />
                    {{ isRecoveryCodesVisible ? 'Hide' : 'View' }} App Recovery
                    Codes
                </Button>

                <Form
                    v-if="isRecoveryCodesVisible && recoveryCodesList.length"
                    v-bind="regenerateRecoveryCodes.form()"
                    method="post"
                    :options="{ preserveScroll: true }"
                    @success="fetchRecoveryCodes"
                    #default="{ processing }"
                >
                    <Button
                        variant="secondary"
                        type="submit"
                        :disabled="processing"
                    >
                        <RefreshCw /> Regenerate App Codes
                    </Button>
                </Form>
            </div>
            <div
                :class="[
                    'relative overflow-hidden transition-all duration-300',
                    isRecoveryCodesVisible
                        ? 'h-auto opacity-100'
                        : 'h-0 opacity-0',
                ]"
            >
                <div v-if="errors?.length" class="mt-6">
                    <AlertError :errors="errors" />
                </div>
                <div v-else class="mt-3 space-y-3">
                    <div
                        ref="recoveryCodeSectionRef"
                        class="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm"
                    >
                        <div v-if="!recoveryCodesList.length" class="space-y-2">
                            <div
                                v-for="n in 8"
                                :key="n"
                                class="h-4 animate-pulse rounded bg-muted-foreground/20"
                            ></div>
                        </div>
                        <div
                            v-else
                            v-for="(code, index) in recoveryCodesList"
                            :key="index"
                        >
                            {{ code }}
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground select-none">
                        Each app recovery code can be used once to access your
                        account and will be removed after use. If you need more,
                        click
                        <span class="font-bold">Regenerate App Codes</span> above.
                    </p>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between mt-6"
            >
                <Button @click="toggleEmailRecoveryCodesVisibility" class="w-fit">
                    <component
                        :is="isEmailRecoveryCodesVisible ? EyeOff : Eye"
                        class="size-4"
                    />
                    {{ isEmailRecoveryCodesVisible ? 'Hide' : 'View' }} Email Recovery
                    Codes
                </Button>

                <Button
                    v-if="isEmailRecoveryCodesVisible && emailRecoveryCodesList.length"
                    variant="secondary"
                    @click="sendEmailRecoveryCodes"
                >
                    <Mail /> Send Email Codes
                </Button>
            </div>
            <div
                :class="[
                    'relative overflow-hidden transition-all duration-300',
                    isEmailRecoveryCodesVisible
                        ? 'h-auto opacity-100'
                        : 'h-0 opacity-0',
                ]"
            >
                <div v-if="errors?.length" class="mt-6">
                    <AlertError :errors="errors" />
                </div>
                <div v-else class="mt-3 space-y-3">
                    <div
                        ref="emailRecoveryCodeSectionRef"
                        class="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm"
                    >
                        <div v-if="!emailRecoveryCodesList.length" class="space-y-2">
                            <div
                                v-for="n in 8"
                                :key="n"
                                class="h-4 animate-pulse rounded bg-muted-foreground/20"
                            ></div>
                        </div>
                        <div
                            v-else
                            v-for="(code, index) in emailRecoveryCodesList"
                            :key="index"
                        >
                            {{ code }}
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground select-none">
                        Each email recovery code can be used once to access your
                        account and will be removed after use. If you need more,
                        you can regenerate them from the 2FA setup modal.
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
