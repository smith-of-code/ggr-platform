<template>
  <RCard elevation="raised">
    <form class="space-y-8" @submit.prevent="submit">
      <div
        v-for="st in stages"
        :key="st.num"
        class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 sm:p-5"
      >
        <p class="text-sm font-semibold text-slate-900">{{ st.title }}</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label :for="`stage-${st.num}-start`" class="mb-1 block text-xs font-medium text-slate-600">Дата начала</label>
            <input
              :id="`stage-${st.num}-start`"
              v-model="form[`stage_${st.num}_deadline_start`]"
              type="date"
              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
            />
            <p v-if="form.errors[`stage_${st.num}_deadline_start`]" class="mt-1 text-xs text-red-600">
              {{ form.errors[`stage_${st.num}_deadline_start`] }}
            </p>
          </div>
          <div>
            <label :for="`stage-${st.num}-end`" class="mb-1 block text-xs font-medium text-slate-600">Дата окончания</label>
            <input
              :id="`stage-${st.num}-end`"
              v-model="form[`stage_${st.num}_deadline_end`]"
              type="date"
              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
            />
            <p v-if="form.errors[`stage_${st.num}_deadline_end`]" class="mt-1 text-xs text-red-600">
              {{ form.errors[`stage_${st.num}_deadline_end`] }}
            </p>
          </div>
        </div>
      </div>
      <div class="flex flex-wrap gap-3">
        <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">Сохранить сроки</RButton>
      </div>
    </form>
  </RCard>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'
import { sameOriginHref } from '@/utils/sameOriginHref.js'

const props = defineProps({
  stages: { type: Array, required: true },
})

function buildFormPayload() {
  return {
    stage_1_deadline_start: props.stages.find((s) => s.num === 1)?.deadline_start || '',
    stage_1_deadline_end: props.stages.find((s) => s.num === 1)?.deadline_end || '',
    stage_2_deadline_start: props.stages.find((s) => s.num === 2)?.deadline_start || '',
    stage_2_deadline_end: props.stages.find((s) => s.num === 2)?.deadline_end || '',
    stage_3_deadline_start: props.stages.find((s) => s.num === 3)?.deadline_start || '',
    stage_3_deadline_end: props.stages.find((s) => s.num === 3)?.deadline_end || '',
  }
}

const form = useForm(buildFormPayload())

watch(
  () => props.stages,
  () => {
    const p = buildFormPayload()
    form.stage_1_deadline_start = p.stage_1_deadline_start
    form.stage_1_deadline_end = p.stage_1_deadline_end
    form.stage_2_deadline_start = p.stage_2_deadline_start
    form.stage_2_deadline_end = p.stage_2_deadline_end
    form.stage_3_deadline_start = p.stage_3_deadline_start
    form.stage_3_deadline_end = p.stage_3_deadline_end
    form.clearErrors()
  },
  { deep: true },
)

function submit() {
  form.put(sameOriginHref(route('admin.tour-cabinet.contest-stage-deadlines.update', undefined, false)), { preserveScroll: true })
}
</script>
