<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Object
});
</script>

<template>
    <Head title="Invoices Uploaded" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Uploaded Invoice Summary</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">Uploaded Invoice Summary (Invoice Index)</div>
                </div>
            </div>
        </div>

        <pre>
            {{invoices}}
        </pre>
    </AuthenticatedLayout>
</template>
