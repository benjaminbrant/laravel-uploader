<script setup>
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    links: Array
});
</script>

<template>
    <div class="border-2 flex flex-row justify-center">
        <div v-for="link in links" :key="link.label" class="monkey">
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
            />
        </div>
    </div>
</template>

<style>
    .monkey {
        width: 75px;
        text-align: center;
    }

    .donkey {
        width: 200px;
        border: 1px solid red;
    }
</style>
