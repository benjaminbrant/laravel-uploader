<script setup>
import {Link} from '@inertiajs/vue3';
import Tick from "@/Components/Tick.vue";
import Cross from "@/Components/Cross.vue";

    const props = defineProps({
        job: Object
    });
</script>

<template>
    <div class="w-full mt-3">
        <div class="bg-white rounded rounded-[15px] sm:w-4/5 md:max-w-[960px] mx-auto drop-shadow">
            <div class="flex">
                <div class="w-[25%] m-3">
                    <div class="border border-green-400 bg-green-400 text-white font-bold p-2 w-fit rounded-full drop-shadow">
<!--                        <a href="">Job-->
<!--                        <span>{{job.id}}</span></a>-->
                        <Link href="/job" method="post" as="button" type="button" :data="{job: job.id}">Job {{job.id}}</Link>
                    </div>
                </div>
                <div v-if="! job.no_invoices_to_process" class="w-[75%] m-3 flex justify-around">
                    <div class="flex flex-col items-center">
                        <div>Payload Success:</div>
                        <div v-if="! job.is_payload_error"><Tick /></div>
                        <div v-else><Cross /></div>
                    </div>
                    <div class="flex flex-col items-center">
                        <div>Upload Success:</div>
                        <div v-if="! job.is_upload_error"><Tick /></div>
                        <div v-else><Cross /></div>
                    </div>
                    <div class="flex flex-col items-center">
                        <div>Archive Success:</div>
                        <div v-if="! job.is_archive_error"><Tick /></div>
                        <div v-else><Cross /></div>
                    </div>
                </div>
                <div v-else class="w-[75%] m-3 flex">
                    <div>No invoices to process.</div>
                </div>
            </div>
            <div v-if="job.is_payload_error || job.is_upload_error || job.is_archive_error" class="p-3">
                <div v-if="job.is_payload_error" >Payload Error Message:<br><span>{{ job.payload_error_msg }}</span></div>
                <div v-if="job.is_upload_error" >Upload Error Message:<br><span>{{ job.upload_error_msg }}</span></div>
                <div v-if="job.is_archive_error" >Archive Error Message:<br><span>{{ job.archive_error_msg }}</span></div>
            </div>
        </div>
    </div>
</template>
