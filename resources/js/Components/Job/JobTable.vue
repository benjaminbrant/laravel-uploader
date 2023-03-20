<script setup>
  const props = defineProps({
      job: Object
  });
</script>

<template>
    <div class="p-2">{{job.id}}</div>
    <div class="p-2">{{job.errors_encountered}}</div>
    <div class="p-2">{{job.created_at}}</div>
</template>
