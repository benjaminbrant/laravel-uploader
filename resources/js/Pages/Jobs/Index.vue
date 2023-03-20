<script setup>
import JobHeader from "@/Components/Job/JobHeader.vue";
import JobTable from "@/Components/Job/JobTable.vue";
import Paginate from "@/Components/Paginate.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    jobs: Object
});
</script>

<template>
    <Head title="Jobs Summary" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Uploaded Jobs Summary</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">Uploaded Jobs Summary (Jobs Index)</div>
                </div>
            </div>
        </div>

        <div v-if="jobs.data">
            <div class="container py-3 m-auto grid grid-cols-3 border-2">
                <JobHeader :headings="['Job ID','Errors Encountered','Created At']" />
            </div>
            <div
                v-for="job in jobs.data"
                :key="job.id"
                class="container py-3 m-auto grid grid-cols-3 border-2"
            >
                <JobTable :job="job" />
            </div>
        </div>
        <div v-else>
            <span>No jobs found</span>
        </div>
        <Paginate
            class="my-6 py-4 rounded"
            :links="jobs.links"
        />


        <pre>
            {{jobs}}
        </pre>
    </AuthenticatedLayout>
</template>
