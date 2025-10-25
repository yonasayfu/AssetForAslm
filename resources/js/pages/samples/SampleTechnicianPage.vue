<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';

const breadcrumbs = [
    { title: 'Samples', href: '/samples' },
    { title: 'Technician Capability Check', href: '/samples/technician' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Technician Capability Sample" />

        <div class="space-y-6">
            <Heading
                title="Technician Capability Sample"
                description="Technicians receive read-only access to the Staff directory and assigned tasks."
            />

            <section class="rounded-lg border border-dashed border-primary/40 bg-card/60 p-6 shadow-sm">
                <h2 class="text-base font-semibold text-foreground">Access Expectations</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>Technicians can open the Staff directory but cannot see Create/Edit buttons.</li>
                    <li>Users, Roles, and Activity Logs are hidden and should return 403 if accessed directly.</li>
                    <li>Dashboard cards and notifications should remain visible.</li>
                </ul>
            </section>

            <section class="rounded-lg border bg-muted/40 p-5">
                <h2 class="text-base font-semibold text-foreground">Suggested Test Flow</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>Visit <code>/staff</code> – confirm the list loads and action buttons are disabled.</li>
                    <li>Open the Staff detail page for a seeded user – editing should be blocked.</li>
                    <li>Directly visit <code>/users</code> – expect a 403 response.</li>
                    <li>Return here to confirm the page is still accessible.</li>
                </ol>
            </section>
        </div>
    </AppLayout>
</template>
