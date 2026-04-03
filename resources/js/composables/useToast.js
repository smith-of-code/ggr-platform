import { ref } from 'vue'

const toasts = ref([])

let nextId = 0

function addToast(message, type = 'error', duration = 6000) {
  const id = ++nextId
  toasts.value.push({ id, message, type })

  if (duration > 0) {
    setTimeout(() => removeToast(id), duration)
  }
}

function removeToast(id) {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

export function useToast() {
  return {
    toasts,
    success: (msg, duration) => addToast(msg, 'success', duration ?? 4000),
    error: (msg, duration) => addToast(msg, 'error', duration),
    warning: (msg, duration) => addToast(msg, 'warning', duration),
    remove: removeToast,
  }
}
