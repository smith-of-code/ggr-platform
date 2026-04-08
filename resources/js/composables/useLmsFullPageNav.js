/**
 * URL под /lms/* — отдельная зона приложения; переход без Inertia (полная загрузка страницы).
 */
export function isLmsFullPageUrl(href) {
  return typeof href === 'string' && href.startsWith('/lms/')
}
