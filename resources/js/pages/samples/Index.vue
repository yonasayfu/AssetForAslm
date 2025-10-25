<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';

const sampleLinks = [
    {
        title: 'Admin Capability Sample',
        description: 'Verifies full control over user, role, and staff management.',
        href: '/samples/admin',
        audience: 'Admin',
    },
    {
        title: 'Manager Capability Sample',
        description: 'Confirms managers can maintain staff records but cannot reconfigure the platform.',
        href: '/samples/manager',
        audience: 'Manager',
    },
    {
        title: 'Technician Capability Sample',
        description: 'Ensures technicians are limited to read-only operational views.',
        href: '/samples/technician',
        audience: 'Technician, Staff, Auditor, ReadOnly',
    },
    {
        title: 'External Viewer Sample',
        description: 'Validates the restricted dashboard-only experience for external stakeholders.',
        href: '/samples/external',
        audience: 'External / Guest',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Samples', href: '/samples' }]">
        <Head title="RBAC Sample Pages" />

        <div class="space-y-6">
            <Heading
                title="RBAC Sample Pages"
                description="Launch these placeholder pages while validating role-based access controls."
            />

            <div class="grid gap-4 md:grid-cols-2">
                <article
                    v-for="sample in sampleLinks"
                    :key="sample.href"
                    class="flex flex-col gap-3 rounded-lg border border-dashed border-primary/30 bg-card/60 p-5 shadow-sm"
                >
                    <h2 class="text-base font-semibold text-foreground">{{ sample.title }}</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ sample.description }}
                    </p>
                    <p class="text-xs font-medium uppercase tracking-wide text-primary">
                        Intended audience: {{ sample.audience }}
                    </p>
                    <div>
                        <a
                            :href="sample.href"
                            class="inline-flex items-center text-sm font-semibold text-primary hover:underline"
                        >
                            Open sample
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </AppLayout>
</template>
