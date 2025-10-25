<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';

const breadcrumbs = [
    { title: 'Samples', href: '/samples' },
    { title: 'Admin Capability Check', href: '/samples/admin' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Capability Sample" />

        <div class="space-y-6">
            <Heading
                title="Admin Capability Sample"
                description="Use this page to confirm that full administrative permissions are wired correctly."
            />

            <section class="rounded-lg border border-dashed border-primary/40 bg-card/60 p-6 shadow-sm">
                <h2 class="text-base font-semibold text-foreground">Access Expectations</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>You should be able to view Staff, Users, Roles, Exports, Notifications, and Activity Logs.</li>
                    <li>You should have access to impersonation tools and mailbox utilities (local only).</li>
                    <li>If any of these capabilities are missing, double-check the `Admin` role sync in <code>RolePermissionSeeder.php</code>.</li>
                </ul>
            </section>

            <section class="rounded-lg border bg-muted/40 p-5">
                <h2 class="text-base font-semibold text-foreground">Quick RBAC Checklist</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>Open a second tab and visit <code>/users</code> &amp; <code>/roles</code> to ensure both load.</li>
                    <li>Visit <code>/staff</code> and confirm you can create/edit records.</li>
                    <li>Use the top-right user menu to impersonate another sample account, then return here.</li>
                    <li>Optional: trigger the API smoke tests <code>./vendor/bin/pest tests/Feature/Api</code>.</li>
                </ol>
            </section>
        </div>
    </AppLayout>
</template>
