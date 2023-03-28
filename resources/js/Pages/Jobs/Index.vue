<script setup>
import Paginate from "@/Components/Paginate.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Job from "@/Components/Job/Job.vue";

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
            <div
                v-for="job in jobs.data"
                :key="job.id"
            >
                <Job :job="job" />
            </div>
        </div>
        <div v-else>
            <span>No jobs found</span>
        </div>
        <Paginate
            class="my-6 py-4 rounded"
            :links="jobs.links"
        />


<!--        <pre>-->
<!--            {{jobs}}-->
<!--        </pre>-->
    </AuthenticatedLayout>
</template>
