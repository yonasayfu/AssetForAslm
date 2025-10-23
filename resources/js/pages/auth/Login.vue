<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, CheckCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const isDialogOpen = ref(false);
const isEmailSent = ref(false);

const forgotPasswordForm = useForm({
    email: '',
});

const submitForgotPassword = () => {
    forgotPasswordForm.post(request().url, {
        preserveScroll: true,
        onSuccess: () => {
            isEmailSent.value = true;
            forgotPasswordForm.clearErrors();
        },
        onError: () => {
            isEmailSent.value = false;
        },
    });
};

const closeDialog = () => {
    isDialogOpen.value = false;
};

watch(isDialogOpen, (open) => {
    if (!open) {
        forgotPasswordForm.reset();
        forgotPasswordForm.clearErrors();
        isEmailSent.value = false;
    }
});
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <Dialog v-if="canResetPassword" v-model:open="isDialogOpen">
                            <DialogTrigger as-child>
                                <button
                                    type="button"
                                    class="rounded-md px-1 text-sm font-medium text-primary transition hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    :tabindex="5"
                                >
                                    Forgot password?
                                </button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-md">
                                <DialogHeader>
                                    <DialogTitle class="text-center">
                                        <span v-if="!isEmailSent">Reset your password</span>
                                        <span v-else class="flex items-center justify-center gap-2">
                                            <CheckCircle class="h-5 w-5 text-green-600" />
                                            Check your email
                                        </span>
                                    </DialogTitle>
                                    <DialogDescription class="text-center">
                                        <span v-if="!isEmailSent">
                                            Enter your email address and we'll send you a password reset link.
                                        </span>
                                        <span v-else>
                                            We've sent a password reset link to your email address.
                                        </span>
                                    </DialogDescription>
                                </DialogHeader>

                                <div v-if="!isEmailSent" class="space-y-4">
                                    <form @submit.prevent="submitForgotPassword" class="space-y-4">
                                        <div class="space-y-2">
                                            <Label for="forgot-email">Email address</Label>
                                            <Input
                                                id="forgot-email"
                                                v-model="forgotPasswordForm.email"
                                                type="email"
                                                required
                                                placeholder="email@example.com"
                                                :disabled="forgotPasswordForm.processing"
                                            />
                                            <InputError :message="forgotPasswordForm.errors.email" />
                                        </div>

                                        <div class="flex gap-2">
                                            <Button
                                                type="submit"
                                                class="flex-1"
                                                :disabled="forgotPasswordForm.processing"
                                            >
                                                <LoaderCircle
                                                    v-if="forgotPasswordForm.processing"
                                                    class="mr-2 h-4 w-4 animate-spin"
                                                />
                                                Send reset link
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                @click="closeDialog"
                                                :disabled="forgotPasswordForm.processing"
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </form>
                                </div>

                                <div v-else class="space-y-4 text-center">
                                    <p class="text-sm text-muted-foreground">
                                        If you don't see the email, check your spam folder or try again.
                                    </p>
                                    <Button class="w-full" @click="closeDialog">
                                        Close
                                    </Button>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Log in
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
