<script setup>
import {Link} from '@inertiajs/vue3';
import {ref} from 'vue';

const search = ref('');
</script>

<template>
    <div class="flex">
        <input class="rounded-full bg-slate-100 border border-slate-300 drop-shadow mr-2" v-model="search" type="text">
        <Link class="bg-green-400 text-white rounded-full drop-shadow font-bold p-2" href="/invoices" method="post" as="button" type="button" :data="{search: search}">Search</Link>
    </div>
</template>
