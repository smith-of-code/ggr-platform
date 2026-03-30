<template>
  <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
})
</script>
