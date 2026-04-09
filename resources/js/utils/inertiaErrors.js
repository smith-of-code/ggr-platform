/**
 * Разбор ошибок для toast: Laravel/Inertia (props.errors), JSON non-Inertia ответов.
 *
 * inertia:invalid — event.detail.response: объект ответа Inertia (data, headers, status),
 *   data уже может быть распарсенным JSON или строкой (HTML / текст).
 * inertia:error — event.detail.errors: объект поле → строка | массив строк.
 */

export const MAX_VALIDATION_MESSAGES = 5
export const MAX_TOAST_CHARS = 400

function truncate(str, maxLen = MAX_TOAST_CHARS) {
    if (str.length <= maxLen) {
        return str
    }
    return `${str.slice(0, maxLen - 1)}…`
}

/**
 * @param {Record<string, string|string[]>|null|undefined} errors
 * @returns {string[]}
 */
export function collectValidationMessages(errors) {
    if (!errors || typeof errors !== 'object') {
        return []
    }
    const out = []
    for (const msgs of Object.values(errors)) {
        const list = Array.isArray(msgs) ? msgs : [msgs]
        for (const m of list) {
            if (typeof m === 'string' && m.trim()) {
                out.push(m.trim())
            }
        }
    }
    return [...new Set(out)]
}

/**
 * @param {Record<string, string|string[]>|null|undefined} errors
 * @returns {string}
 */
export function formatValidationErrors(errors) {
    const lines = collectValidationMessages(errors)
    if (lines.length === 0) {
        return 'Проверьте правильность заполнения формы'
    }
    const shown = lines.slice(0, MAX_VALIDATION_MESSAGES)
    let text = shown.join(' • ')
    if (lines.length > MAX_VALIDATION_MESSAGES) {
        text += ` (ещё ${lines.length - MAX_VALIDATION_MESSAGES})`
    }
    return truncate(text)
}

/**
 * @param {unknown} data — распарсенный JSON
 * @returns {string|null}
 */
function messageFromJsonPayload(data) {
    if (!data || typeof data !== 'object') {
        return null
    }
    if (data.errors && typeof data.errors === 'object') {
        const fromVal = formatValidationErrors(data.errors)
        if (fromVal !== 'Проверьте правильность заполнения формы') {
            return fromVal
        }
    }
    if (typeof data.message === 'string' && data.message.trim()) {
        return truncate(data.message.trim())
    }
    return null
}

/**
 * Non-Inertia ответ (после preventDefault на inertia:invalid).
 * @param {{ data?: unknown, headers?: Record<string, string> }|null|undefined} responseLike
 * @returns {string}
 */
export function formatInvalidInertiaResponse(responseLike) {
    const fallback = 'Сервер вернул некорректный ответ'
    if (!responseLike || typeof responseLike !== 'object') {
        return fallback
    }

    const { data, headers } = responseLike
    const contentType =
        (headers && (headers['content-type'] ?? headers['Content-Type'])) || ''

    if (data && typeof data === 'object') {
        const msg = messageFromJsonPayload(data)
        if (msg) {
            return msg
        }
    }

    if (typeof data === 'string') {
        const trimmed = data.trim()
        if (contentType.includes('application/json') || trimmed.startsWith('{') || trimmed.startsWith('[')) {
            try {
                const parsed = JSON.parse(trimmed)
                return messageFromJsonPayload(parsed) ?? fallback
            } catch {
                return fallback
            }
        }
        if (trimmed.startsWith('<') || /<!doctype/i.test(trimmed)) {
            return fallback
        }
        if (trimmed.length > 0 && trimmed.length < 500) {
            return truncate(trimmed)
        }
    }

    return fallback
}
