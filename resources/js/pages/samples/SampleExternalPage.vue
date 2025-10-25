<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';

const breadcrumbs = [
    { title: 'Samples', href: '/samples' },
    { title: 'External Viewer Check', href: '/samples/external' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="External Viewer Sample" />

        <div class="space-y-6">
            <Heading
                title="External Viewer Sample"
                description="External or guest users should see dashboards only and be blocked from management screens."
            />

            <section class="rounded-lg border border-dashed border-primary/40 bg-card/60 p-6 shadow-sm">
                <h2 class="text-base font-semibold text-foreground">Access Expectations</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>No sidebar links beyond Dashboard, Notifications, and personal Settings.</li>
                    <li>Direct navigation to Staff, Users, or Roles should redirect to Dashboard or return 403.</li>
                    <li>This page confirms the user can load permitted informational content.</li>
                </ul>
            </section>

            <section class="rounded-lg border bg-muted/40 p-5">
                <h2 class="text-base font-semibold text-foreground">Suggested Test Flow</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>Log in as an External or ReadOnly user.</li>
                    <li>Observe the sidebar: only Dashboard and Settings pages should appear.</li>
                    <li>Attempt to visit <code>/staff</code> or <code>/users</code>; verify access is denied.</li>
                    <li>Return here to confirm the route remains visible.</li>
                </ol>
            </section>

            <div class="rounded-lg border border-dashed border-muted-foreground/40 bg-muted/10 p-4 text-sm text-muted-foreground">
                Need a sample External account? Log in as <code>readonly@example.com</code> with password <code>password</code>, then update the role to <strong>External</strong> using an Admin session.
            </div>
        </div>
    </AppLayout>
</template>
