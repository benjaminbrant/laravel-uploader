<script setup>
const props = defineProps({
    invoiceAddress: String
});
</script>

<template>
    <a
        v-if="invoiceAddress"
        :href="invoiceAddress"
        target="_blank"
    >
        Open Invoice
    </a>
</template>
