<template>
  <div class="min-h-screen bg-gray-50 px-4 py-12">
    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white shadow-lg">
      <FormRenderer :form="form" :fields="fields" />
    </div>
  </div>
</template>

<script setup>
import FormRenderer from '@/Components/FormRenderer.vue'

defineProps({
  form: { type: Object, required: true },
  fields: { type: Array, required: true },
})
</script>
