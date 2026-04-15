const pad = (n) => String(n).padStart(2, '0')

/**
 * ISO 8601 из API → значение для input[type="datetime-local"] (локальное время браузера).
 */
export function lmsDeadlineToDatetimeLocal(isoString) {
  if (!isoString) return ''
  const d = new Date(isoString)
  if (Number.isNaN(d.getTime())) return ''
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

/**
 * @param {'long'|'short'} variant — long: длинный месяц; short: dd.mm.yyyy
 */
export function formatLmsAssignmentDeadline(isoString, variant = 'long') {
  if (!isoString) return '—'
  const d = new Date(isoString)
  if (Number.isNaN(d.getTime())) return String(isoString)
  return d.toLocaleString('ru-RU', {
    day: variant === 'short' ? '2-digit' : 'numeric',
    month: variant === 'short' ? '2-digit' : 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
