<script setup>
import { computed } from 'vue';
import Tick from "@/Components/Tick.vue";
import Cross from "@/Components/Cross.vue";

    const props = defineProps({
        invoice: Object
    });

    const createdAt = computed(() => {
        let created = new Date(props.invoice.created_at);
        return created.toLocaleString('en-GB', {year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
    });

    const updatedAt = computed(() => {
        let updated = new Date(props.invoice.updated_at);
        return updated.toLocaleString('en-GB', {year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
    });


</script>

<template>
    <div class="w-full py-2">
        <div class="md:w-4/5 lg:max-w-[960px] rounded-[15px] bg-white mx-auto p-4 flex drop-shadow">
            <div class="w-[75%]">
                <div class="flex flex-col md:flex-row md:justify-start p-3">
                        <span class="border border-green-400 rounded-full bg-green-400 text-white p-3 font-bold drop-shadow-md w-fit">{{ invoice.po }}</span>
                </div>
                <div class="p-3">
                    <div class="w-fit"><span>Created At: </span><span class="font-bold">{{ createdAt }}</span></div>
                    <div class="w-fit pt-1"><span>Updated At: </span><span class="font-bold">{{ updatedAt }}</span></div>
                </div>
                <div class="md:flex md:justify-start p-3">
                    <div class=""><span>Local Filesize: </span><span class="font-bold">{{ invoice.local_size }}</span></div>
                    <div class="md:pt-0 md:pl-2 pt-1"><span>Remote Filesize: </span><span class="font-bold">{{ invoice.remote_size }}</span></div>
                    <div class="md:pt-0 md:pl-2 pt-1 flex">
                        <span class="md:mr-2">Sizes Match: </span>
                        <span v-if="invoice.is_identical_filesize"><Tick /></span>
                        <span v-else><Cross /></span>
                    </div>
                </div>
            </div>
            <div class="md:w-[25%]">
                <div class="p-3 h-full flex justify-center items-center">
                    <div class="border border-green-400 rounded-full p-3 bg-green-400 text-white font-bold drop-shadow text-center">
                        <a :href="invoice.archive_location" target="_blank">Open Invoice</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
