/**
 * Resolves a file reference (path or URL) into a usable URL.
 *
 * Handles three formats:
 * - Full URL (http/https): returned as-is
 * - Absolute path (/storage/...): returned as-is
 * - Relative path (avatars/xxx.jpg): prefixed with /storage/
 */
export function fileUrl(pathOrUrl) {
    if (!pathOrUrl) return null
    if (pathOrUrl.startsWith('http') || pathOrUrl.startsWith('/')) return pathOrUrl
    return `/storage/${pathOrUrl}`
}
