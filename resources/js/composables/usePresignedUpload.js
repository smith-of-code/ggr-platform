import { ref } from 'vue'
import axios from 'axios'

/**
 * @param {object} options
 * @param {string} options.presignedUrlEndpoint - POST endpoint to get presigned URL
 * @param {string} options.confirmEndpoint - POST endpoint to confirm upload
 * @param {string} [options.fallbackUploadUrl] - traditional upload URL (used when mode === 'server')
 * @param {string} [options.fieldName='image'] - form field name for fallback upload
 * @param {string} [options.collection]
 * @param {string} [options.entityType]
 * @param {string|number} [options.entityId]
 */
export function usePresignedUpload(options = {}) {
  const progress = ref(0)
  const uploading = ref(false)
  const error = ref('')

  async function uploadFile(file, overrides = {}) {
    const opts = { ...options, ...overrides }
    progress.value = 0
    uploading.value = true
    error.value = ''

    try {
      const { data: presigned } = await axios.post(opts.presignedUrlEndpoint, {
        filename: file.name,
        content_type: file.type || 'application/octet-stream',
        size: file.size,
        directory: opts.directory || null,
        collection: opts.collection || null,
      })

      if (presigned.mode === 'server') {
        return await serverUpload(file, opts)
      }

      await putToS3(presigned.url, file, presigned.headers || {})

      const { data: confirmed } = await axios.post(opts.confirmEndpoint, {
        key: presigned.key,
        original_name: file.name,
        content_type: file.type || 'application/octet-stream',
        size: file.size,
        collection: opts.collection || null,
        entity_type: opts.entityType || null,
        entity_id: opts.entityId || null,
      })

      progress.value = 100
      return confirmed
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Ошибка загрузки'
      error.value = msg
      throw err
    } finally {
      uploading.value = false
    }
  }

  function putToS3(url, file, headers) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest()
      xhr.open('PUT', url, true)

      Object.entries(headers).forEach(([key, value]) => {
        xhr.setRequestHeader(key, value)
      })

      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
          progress.value = Math.round((e.loaded / e.total) * 95)
        }
      }

      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          resolve()
        } else {
          reject(new Error(`S3 upload failed: ${xhr.status}`))
        }
      }

      xhr.onerror = () => reject(new Error('Network error during upload'))
      xhr.send(file)
    })
  }

  async function serverUpload(file, opts) {
    const uploadUrl = opts.fallbackUploadUrl || opts.presignedUrlEndpoint.replace('/presigned-url', '/image')
    const fieldName = opts.fieldName || 'image'

    const formData = new FormData()
    formData.append(fieldName, file)
    if (opts.collection) formData.append('collection', opts.collection)
    if (opts.entityType) formData.append('entity_type', opts.entityType)
    if (opts.entityId) formData.append('entity_id', opts.entityId)

    const { data } = await axios.post(uploadUrl, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (e) => {
        if (e.lengthComputable) {
          progress.value = Math.round((e.loaded / e.total) * 100)
        }
      },
    })

    return data
  }

  return { uploadFile, progress, uploading, error }
}
