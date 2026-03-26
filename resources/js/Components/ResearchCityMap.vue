<template>
  <div class="relative">
    <div ref="mapContainer" class="h-full w-full rounded-2xl" :style="{ minHeight: height }" />
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center rounded-2xl bg-white/60 backdrop-blur-sm">
      <div class="flex items-center gap-3 text-sm font-medium text-gray-500">
        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        Загрузка карты…
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  cities: { type: Array, required: true },
  height: { type: String, default: '480px' },
})

const emit = defineEmits(['select-city'])

const mapContainer = ref(null)
const loading = ref(true)
let mapInstance = null

function loadYmaps() {
  return new Promise((resolve) => {
    if (window.ymaps) {
      window.ymaps.ready(resolve)
      return
    }
    const script = document.createElement('script')
    script.src = 'https://api-maps.yandex.ru/2.1/?apikey=2ef4a5ed-4e09-49b4-8fb4-e7b5ccba8b9b&lang=ru_RU'
    script.async = true
    script.onload = () => window.ymaps.ready(resolve)
    document.head.appendChild(script)
  })
}

function researchWord(n) {
  const x = Math.abs(Number(n)) % 100
  const y = x % 10
  if (x > 10 && x < 20) return 'исследований'
  if (y > 1 && y < 5) return 'исследования'
  if (y === 1) return 'исследование'
  return 'исследований'
}

function buildBalloon(city) {
  const count = city.researches_count || 0

  const researchList = (city.latest_researches || [])
    .map(
      (r) =>
        `<a href="/research/${r.slug}" style="display:block;padding:6px 0;border-bottom:1px solid #f3f4f6;color:#1f2937;text-decoration:none;font-size:13px;line-height:1.4;transition:color .15s" onmouseover="this.style.color='#003274'" onmouseout="this.style.color='#1f2937'">
          <span style="font-weight:600">${r.title}</span>
          ${r.year ? `<span style="color:#9ca3af;font-size:11px;margin-left:6px">${r.year}</span>` : ''}
        </a>`,
    )
    .join('')

  const img = city.image
    ? `<div style="width:100%;height:100px;overflow:hidden;border-radius:8px 8px 0 0;margin:-12px -12px 10px"><img src="${city.image}" style="width:100%;height:100%;object-fit:cover" alt="" /></div>`
    : ''

  return `
    <div style="max-width:300px;font-family:system-ui,sans-serif;padding:12px">
      ${img}
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
        <p style="margin:0;font-size:17px;font-weight:700;color:#003274">${city.name}</p>
        <span style="display:inline-flex;align-items:center;justify-content:center;background:#003274;color:#fff;font-size:11px;font-weight:700;border-radius:999px;min-width:22px;height:22px;padding:0 6px">${count}</span>
      </div>
      ${city.region ? `<p style="margin:0 0 8px;color:#9ca3af;font-size:12px">${city.region}</p>` : ''}
      <p style="margin:0 0 6px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280">${count} ${researchWord(count)}</p>
      ${researchList ? `<div style="margin-bottom:10px">${researchList}</div>` : ''}
      <div style="display:flex;gap:6px">
        <a href="/research?city=${city.id}"
           style="display:inline-block;padding:6px 14px;background:#003274;color:#fff;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600">
          Все исследования
        </a>
        <a href="/cities/${city.slug}"
           style="display:inline-block;padding:6px 14px;background:#f3f4f6;color:#374151;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600">
          О городе
        </a>
      </div>
    </div>
  `
}

function markerColor(count) {
  if (count >= 5) return '#003274'
  if (count >= 3) return '#025ea1'
  if (count >= 2) return '#0277bd'
  return '#0288d1'
}

async function initMap() {
  if (!mapContainer.value || !props.cities.length) {
    loading.value = false
    return
  }

  loading.value = true
  await loadYmaps()

  const ymaps = window.ymaps

  if (mapInstance) {
    mapInstance.destroy()
    mapInstance = null
  }

  mapInstance = new ymaps.Map(
    mapContainer.value,
    {
      center: [56.5, 44.0],
      zoom: 4,
      controls: ['zoomControl', 'fullscreenControl'],
    },
    { suppressMapOpenBlock: true },
  )

  const clusterer = new ymaps.Clusterer({
    preset: 'islands#invertedDarkBlueClusterIcons',
    groupByCoordinates: false,
    clusterDisableClickZoom: false,
    clusterBalloonContentLayout: 'cluster#balloonCarousel',
  })

  const placemarks = props.cities.map((city) => {
    const count = city.researches_count || 0
    const color = markerColor(count)

    const pm = new ymaps.Placemark(
      [Number(city.lat), Number(city.lng)],
      {
        balloonContentBody: buildBalloon(city),
        hintContent: `${city.name} — ${count} ${researchWord(count)}`,
      },
      {
        preset: 'islands#dotCircleIcon',
        iconColor: color,
      },
    )

    pm.events.add('click', (e) => {
      e.get('target').balloon.open()
    })

    return pm
  })

  clusterer.add(placemarks)
  mapInstance.geoObjects.add(clusterer)

  if (props.cities.length > 1) {
    mapInstance.setBounds(clusterer.getBounds(), {
      checkZoomRange: true,
      zoomMargin: 60,
    })
  } else {
    mapInstance.setCenter(
      [Number(props.cities[0].lat), Number(props.cities[0].lng)],
      10,
    )
  }

  loading.value = false
}

onMounted(initMap)

onBeforeUnmount(() => {
  if (mapInstance) {
    mapInstance.destroy()
    mapInstance = null
  }
})

watch(
  () => props.cities,
  () => {
    if (mapInstance) {
      mapInstance.destroy()
      mapInstance = null
    }
    initMap()
  },
  { deep: true },
)
</script>
