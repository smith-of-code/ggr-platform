import { onMounted, onUnmounted, nextTick } from 'vue'

export function useScrollReveal() {
  let intersectionObserver = null
  let mutationObserver = null
  let fallbackTimer = null

  function observeNewElements() {
    if (!intersectionObserver) return
    const fresh = document.querySelectorAll('.reveal:not(.revealed):not([data-reveal-observed])')
    fresh.forEach((el) => {
      el.setAttribute('data-reveal-observed', '')
      intersectionObserver.observe(el)
    })
    if (fresh.length) scheduleFallback()
  }

  function scheduleFallback() {
    clearTimeout(fallbackTimer)
    fallbackTimer = setTimeout(() => {
      document.querySelectorAll('.reveal:not(.revealed)').forEach((el) => {
        const rect = el.getBoundingClientRect()
        if (rect.top < window.innerHeight && rect.bottom > 0) {
          el.classList.add('revealed')
          if (intersectionObserver) intersectionObserver.unobserve(el)
        }
      })
    }, 300)
  }

  onMounted(() => {
    intersectionObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed')
            intersectionObserver.unobserve(entry.target)
          }
        })
      },
      { threshold: 0.05, rootMargin: '0px 0px -20px 0px' }
    )

    nextTick(() => observeNewElements())

    mutationObserver = new MutationObserver(() => {
      nextTick(() => observeNewElements())
    })
    mutationObserver.observe(document.body, { childList: true, subtree: true })
  })

  onUnmounted(() => {
    if (intersectionObserver) intersectionObserver.disconnect()
    if (mutationObserver) mutationObserver.disconnect()
    clearTimeout(fallbackTimer)
  })
}
