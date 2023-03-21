<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import InvoiceHeader from "@/Components/Invoice/InvoiceHeader.vue";
import InvoiceTable from "@/Components/Invoice/InvoiceTable.vue";
import Paginate from "@/Components/Paginate.vue";

const headings = [
    "ID",
    "Job ID",
    "PO",
    "Filename",
    "Local Size",
    "Remote Size",
    "Uploaded",
    "Processed",
    "Identical Filesize",
    "Archival Location",
    "Archival Error",
    "Created At",
    "Updated At"
];

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

        <div v-if="invoices.data">
            <div class="w-screen py-3 border-2 grid grid-cols-13">
                <InvoiceHeader :headings="headings" />
            </div>
            <div
                v-for="invoice in invoices.data"
                :key="invoice.id"
                class="w-screen py-3 border-2 grid grid-cols-13"
            >
                <InvoiceTable :invoice="invoice" />
            </div>
        </div>
        <div v-else>
            <span>No invoices found</span>
        </div>
        <Paginate
            class="my-6 py-4 rounded"
            :links="invoices.links"
        />

<!--        <pre>-->
<!--            {{invoices}}-->
<!--        </pre>-->
    </AuthenticatedLayout>
</template>
