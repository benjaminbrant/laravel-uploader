<script setup>
  import InvoiceLink from "@/Components/Invoice/InvoiceLink.vue";

  const props = defineProps({
      invoice: Object
  });
</script>

<template>
    <div class="p-2 text-center">{{ invoice.id }}</div>
    <div class="p-2 text-center">{{ invoice.job_id }}</div>
    <div class="p-2 text-center">{{ invoice.po }}</div>
    <div class="p-2 text-center">{{ invoice.filename }}</div>
    <div class="p-2 text-center">{{ invoice.local_size }}</div>
    <div class="p-2 text-center">{{ invoice.remote_size }}</div>
    <div class="p-2 text-center">{{ invoice.is_uploaded }}</div>
    <div class="p-2 text-center">{{ invoice.is_processed }}</div>
    <div class="p-2 text-center">{{ invoice.is_identical_filesize }}</div>
    <div class="p-2 text-center"><InvoiceLink :invoiceAddress="invoice.archive_location" /></div>
    <div class="p-2 text-center">{{ invoice.archival_error }}</div>
    <div class="p-2 text-center">{{ invoice.created_at }}</div>
    <div class="p-2 text-center">{{ invoice.updated_at }}</div>
</template>


