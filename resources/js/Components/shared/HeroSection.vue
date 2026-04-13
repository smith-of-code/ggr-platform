<template>
  <section
    class="relative overflow-hidden px-4 sm:px-6 lg:px-8"
    :class="[paddingClass, sectionColorClass]"
    :style="sectionStyle"
  >
    <div v-if="bgImage && bgImageInline" class="absolute inset-0" :style="inlineOverlayStyle" />

    <img
      v-if="bgImage && !bgImageInline"
      :src="bgImage"
      alt=""
      class="absolute inset-0 h-full w-full object-cover opacity-15 mix-blend-luminosity"
    />

    <div v-if="overlay" class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.1),transparent_70%)]" />

    <slot name="decorations" />

    <div class="relative mx-auto max-w-7xl" :class="{ 'text-center': centered }">
      <p v-if="eyebrow" class="text-sm font-semibold uppercase tracking-widest" :style="{ color: eyebrowColor }">
        {{ eyebrow }}
      </p>

      <h1
        class="font-bold tracking-tight"
        :class="[eyebrow ? 'mt-3' : '', titleSizeClass]"
      >
        <slot name="title">{{ title }}</slot>
      </h1>

      <p
        v-if="description"
        class="mt-6 max-w-3xl text-lg leading-relaxed"
        :class="{ 'mx-auto': centered }"
        :style="{ color: descriptionColor }"
      >
        {{ description }}
      </p>

      <slot />
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, default: '' },
  eyebrow: { type: String, default: '' },
  description: { type: String, default: '' },
  bgImage: { type: String, default: '' },
  bgImageInline: { type: Boolean, default: false },
  overlay: { type: Boolean, default: false },
  centered: { type: Boolean, default: false },
  size: { type: String, default: 'md', validator: v => ['sm', 'md', 'lg'].includes(v) },
  bgColorFrom: { type: String, default: '' },
  bgColorVia: { type: String, default: '' },
  bgColorTo: { type: String, default: '' },
  textColor: { type: String, default: '' },
  bgColorEnabled: { type: Boolean, default: false },
})

const useCustomBg = computed(() => props.bgColorEnabled && props.bgColorFrom)

const hasBgImageInline = computed(() => props.bgImage && props.bgImageInline)

const sectionColorClass = computed(() => {
  if (useCustomBg.value || hasBgImageInline.value) return ''
  return 'bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] text-white'
})

const customGradient = computed(() => {
  if (!useCustomBg.value) return ''
  const from = props.bgColorFrom
  const via = props.bgColorVia
  const to = props.bgColorTo || from
  const stops = via ? `${from}, ${via}, ${to}` : `${from}, ${to}`
  return `linear-gradient(to bottom right, ${stops})`
})

const sectionStyle = computed(() => {
  const style = {}
  if (hasBgImageInline.value) {
    style.backgroundImage = `url(${props.bgImage})`
    style.backgroundSize = 'cover'
    style.backgroundPosition = 'center'
  } else if (useCustomBg.value) {
    style.backgroundImage = customGradient.value
  }
  if (props.textColor) {
    style.color = props.textColor
  }
  if (!props.textColor && (useCustomBg.value || hasBgImageInline.value)) {
    style.color = '#ffffff'
  }
  return style
})

const inlineOverlayStyle = computed(() => {
  if (useCustomBg.value) {
    return { backgroundImage: customGradient.value, opacity: 0.85 }
  }
  return { backgroundColor: '#003274b3' }
})

const eyebrowColor = computed(() =>
  props.textColor ? `${props.textColor}b3` : 'rgba(255,255,255,0.7)',
)

const descriptionColor = computed(() =>
  props.textColor ? `${props.textColor}d9` : 'rgba(255,255,255,0.85)',
)

const paddingClass = computed(() => ({
  sm: 'py-16 sm:py-20 lg:py-24',
  md: 'py-20',
  lg: 'py-24 sm:py-32 lg:py-40',
}[props.size]))

const titleSizeClass = computed(() => ({
  sm: 'text-3xl sm:text-4xl lg:text-5xl',
  md: 'text-3xl sm:text-4xl lg:text-5xl',
  lg: 'text-4xl sm:text-5xl lg:text-6xl',
}[props.size]))
</script>
