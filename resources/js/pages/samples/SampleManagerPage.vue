<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';

const breadcrumbs = [
    { title: 'Samples', href: '/samples' },
    { title: 'Manager Capability Check', href: '/samples/manager' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Manager Capability Sample" />

        <div class="space-y-6">
            <Heading
                title="Manager Capability Sample"
                description="Managers should be able to oversee staff records but not manage platform configuration."
            />

            <section class="rounded-lg border border-dashed border-primary/40 bg-card/60 p-6 shadow-sm">
                <h2 class="text-base font-semibold text-foreground">Access Expectations</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>Manager accounts see the Staff directory with create/update options.</li>
                    <li>Users, Roles, and Activity Log pages should return a 403 (permission denied).</li>
                    <li>Exports &amp; notifications remain visible for operational oversight.</li>
                </ul>
            </section>

            <section class="rounded-lg border bg-muted/40 p-5">
                <h2 class="text-base font-semibold text-foreground">Suggested Test Flow</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>Navigate to <code>/staff</code> – confirm you can add a record.</li>
                    <li>Navigate to <code>/users</code> – expect a 403 response.</li>
                    <li>Attempt to reach <code>/roles</code> – expect a 403 response.</li>
                    <li>Open this page again; if you were redirected away, the role assignment is incorrect.</li>
                </ol>
            </section>
        </div>
    </AppLayout>
</template>
