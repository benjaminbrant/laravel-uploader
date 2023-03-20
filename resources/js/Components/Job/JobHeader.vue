<script setup>
    const props = defineProps({
        headings: Array
    });
</script>

<template>
    <div class="text-center">{{headings[0]}}</div>
    <div class="text-center">{{headings[1]}}</div>
    <div class="text-center">{{headings[2]}}</div>
</template>
