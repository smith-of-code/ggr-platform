/**
 * Ziggy в @routes может собирать абсолютные URL с APP_URL другого хоста (например lms при работе с main).
 * Inertia тогда делает XHR на чужой origin → CORS и редирект на /login того хоста.
 * Возвращаем путь на текущем origin: /lms-admin/... или /forms/...
 */
export function sameOriginHref(url) {
  if (typeof url !== 'string' || url === '') {
    return url
  }
  if (url.startsWith('/') && !url.startsWith('//')) {
    return url
  }
  if (typeof window === 'undefined') {
    return url
  }
  try {
    const u = new URL(url)
    if (u.origin !== window.location.origin) {
      return `${u.pathname}${u.search}${u.hash}`
    }
    return `${u.pathname}${u.search}${u.hash}`
  } catch {
    return url
  }
}
