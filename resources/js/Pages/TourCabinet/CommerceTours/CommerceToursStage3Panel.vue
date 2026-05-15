<template>
  <div class="space-y-4">
    <p
      v-if="locked"
      class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
    >
      Этот этап станет доступен после успешной отправки анкеты на этапе 2.
    </p>

    <template v-else>
      <div class="space-y-2">
        <h4 class="text-base font-semibold text-slate-900">{{ subject || 'Заявка принята' }}</h4>
        <p class="whitespace-pre-line text-sm text-slate-700">{{ body }}</p>
      </div>

      <div v-if="canArchive" class="border-t border-slate-200 pt-4">
        <p class="text-sm text-slate-600">
          Сохраните текущую заявку в «Архив коммерческих туров», чтобы оформить новую.
          Архив доступен только для просмотра — данные не потеряются.
        </p>
        <div class="mt-3">
          <RButton type="button" variant="primary" size="sm" :disabled="archiving" @click="archiveAndReset">
            <ArchiveBoxIcon class="mr-1.5 h-4 w-4" aria-hidden="true" />
            <span v-if="!archiving">Сохранить в архив и оформить новую заявку</span>
            <span v-else>Сохранение…</span>
          </RButton>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { ArchiveBoxIcon } from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  subject: { type: String, default: '' },
  body: { type: String, default: '' },
  locked: { type: Boolean, default: false },
  currentStage: { type: Number, default: 1 },
})

const canArchive = computed(() => !props.locked && Number(props.currentStage) >= 3)

const archiving = ref(false)
function archiveAndReset() {
  if (archiving.value) return
  archiving.value = true
  router.post(
    route('tour-cabinet.commerce-tours.archive-and-reset'),
    {},
    {
      onFinish: () => {
        archiving.value = false
      },
    },
  )
}
</script>
