<template>
  <div ref="mapContainer" class="h-full w-full rounded-2xl" />
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  cities: { type: Array, required: true },
  height: { type: String, default: '500px' },
})

const mapContainer = ref(null)
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

function buildBalloon(city) {
  const img = city.image
    ? `<div style="width:100%;height:120px;overflow:hidden;border-radius:8px;margin-bottom:8px"><img src="${city.image}" style="width:100%;height:100%;object-fit:cover" alt="" /></div>`
    : ''
  const pop = city.population
    ? `<p style="margin:4px 0 0;color:#6b7280;font-size:13px">${Number(city.population).toLocaleString('ru-RU')} жителей</p>`
    : ''
  const region = city.region
    ? `<p style="margin:2px 0 0;color:#9ca3af;font-size:12px">${city.region}</p>`
    : ''

  return `
    <div style="max-width:260px;font-family:system-ui,sans-serif">
      ${img}
      <p style="margin:0;font-size:16px;font-weight:700;color:#003274">${city.name}</p>
      ${region}
      ${pop}
      <a href="/cities/${city.slug}"
         style="display:inline-block;margin-top:10px;padding:6px 16px;background:#003274;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600">
        Открыть город
      </a>
    </div>
  `
}

async function initMap() {
  if (!mapContainer.value || !props.cities.length) return

  await loadYmaps()

  const ymaps = window.ymaps

  const center = props.cities.length === 1
    ? [Number(props.cities[0].lat), Number(props.cities[0].lng)]
    : [56.5, 44.0]

  mapInstance = new ymaps.Map(mapContainer.value, {
    center,
    zoom: 4,
    controls: ['zoomControl', 'fullscreenControl'],
  }, {
    suppressMapOpenBlock: true,
  })

  const clusterer = new ymaps.Clusterer({
    preset: 'islands#invertedDarkBlueClusterIcons',
    groupByCoordinates: false,
    clusterDisableClickZoom: false,
    clusterBalloonContentLayout: 'cluster#balloonCarousel',
  })

  const placemarks = props.cities.map((city) => {
    const pm = new ymaps.Placemark(
      [Number(city.lat), Number(city.lng)],
      {
        balloonContentBody: buildBalloon(city),
        hintContent: city.name,
        citySlug: city.slug,
      },
      {
        preset: 'islands#governmentCircleIcon',
        iconColor: '#003274',
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
  }
}

onMounted(initMap)

onBeforeUnmount(() => {
  if (mapInstance) {
    mapInstance.destroy()
    mapInstance = null
  }
})

watch(() => props.cities, () => {
  if (mapInstance) {
    mapInstance.destroy()
    mapInstance = null
  }
  initMap()
}, { deep: true })
</script>
