import { onMounted, onUnmounted, nextTick } from 'vue'

export function useScrollReveal() {
  let intersectionObserver = null
  let mutationObserver = null

  function observeNewElements() {
    if (!intersectionObserver) return
    document.querySelectorAll('.reveal:not(.revealed):not([data-reveal-observed])').forEach((el) => {
      el.setAttribute('data-reveal-observed', '')
      intersectionObserver.observe(el)
    })
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
      { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
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
  })
}
