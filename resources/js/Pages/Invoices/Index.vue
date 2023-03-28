<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Paginate from "@/Components/Paginate.vue";
import Invoice from "@/Components/Invoice/Invoice.vue";

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

        <div v-if="invoices.total > 0">
            <div
                v-for="invoice in invoices.data"
                :key="invoice.id"
            >
                <Invoice :invoice="invoice" />
            </div>
        </div>
        <div v-else class="w-1/2 bg-white rounded h-12 flex justify-center items-center mx-auto drop-shadow">
            <span class="font-bold">No invoices found</span>
        </div>
        <Paginate
            class="my-6 py-4 rounded"
            :links="invoices.links"
        />
    </AuthenticatedLayout>
</template>
