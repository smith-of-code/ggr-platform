/**
 * LMS: блок этапа type=file — в `content` JSON { url, name } либо legacy — одна строка URL.
 */

export function parseLmsStageFileBlockContent(raw) {
  if (raw == null) return { url: '', name: '' }
  const s = String(raw).trim()
  if (!s) return { url: '', name: '' }
  if (s.startsWith('{')) {
    try {
      const o = JSON.parse(s)
      if (o && typeof o === 'object' && typeof o.url === 'string') {
        return {
          url: o.url.trim(),
          name: typeof o.name === 'string' ? o.name.trim() : '',
        }
      }
    } catch {
      /* ignore */
    }
  }
  return { url: s, name: '' }
}

export function encodeLmsStageFileBlockContent(url, name) {
  const u = (url != null ? String(url) : '').trim()
  const n = (name != null ? String(name) : '').trim().slice(0, 500)
  return JSON.stringify({ url: u, name: n })
}
