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
 * ISO из API (UTC, с Z) → datetime-local с теми же часами, что в UTC (без сдвига в TZ браузера).
 * Для админки дедлайна: «9:00 в БД UTC» = «9:00» в поле.
 */
export function lmsDeadlineToDatetimeLocalUtc(isoString) {
  if (!isoString) return ''
  const d = new Date(isoString)
  if (Number.isNaN(d.getTime())) return ''
  return `${d.getUTCFullYear()}-${pad(d.getUTCMonth() + 1)}-${pad(d.getUTCDate())}T${pad(d.getUTCHours())}:${pad(d.getUTCMinutes())}`
}

/**
 * Значение из datetime-local (YYYY-MM-DDTHH:mm) → строка для Laravel как момент UTC (суффикс Z).
 * Пустая строка → null.
 */
export function datetimeLocalToUtcIso(localValue) {
  if (localValue === null || localValue === undefined) return null
  const s = String(localValue).trim()
  if (s === '') return null
  if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(s)) {
    return `${s}:00Z`
  }
  return localValue
}

/**
 * Дедлайн задания для отображения в UI.
 * Используется календарь UTC (как в БД / в админ-поле дедлайна), без сдвига в часовой пояс браузера.
 *
 * @param {'long'|'short'} variant — long: длинный месяц; short: dd.mm.yyyy
 */
export function formatLmsAssignmentDeadline(isoString, variant = 'long') {
  if (!isoString) return '—'
  const d = new Date(isoString)
  if (Number.isNaN(d.getTime())) return String(isoString)
  return d.toLocaleString('ru-RU', {
    timeZone: 'UTC',
    day: variant === 'short' ? '2-digit' : 'numeric',
    month: variant === 'short' ? '2-digit' : 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
