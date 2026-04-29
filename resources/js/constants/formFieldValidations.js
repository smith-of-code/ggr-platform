/**
 * Каталог пресетов валидации для полей формы — зеркало
 * App\Services\Lms\Forms\FieldValidationPresets (PHP).
 *
 * Используется в:
 *   - Pages/Lms/Admin/Forms/Form.vue (селектор «Тип валидации»)
 *   - Components/FormRenderer.vue (пред-валидация на клиенте)
 *
 * Каждый пресет: { value, label, message, validate(value) -> boolean }.
 * Функция validate возвращает true для валидного и пустого значения
 * (обязательность контролируется флагом required отдельно).
 */

const digits = (v) => String(v ?? '').replace(/\D/gu, '')

const isEmpty = (v) => v === null || v === undefined || (typeof v === 'string' && v.trim() === '')

function validateSnils(v) {
  if (isEmpty(v)) return true
  const d = digits(v)
  if (d.length !== 11) return false
  const number = parseInt(d.slice(0, 9), 10)
  const checkSum = parseInt(d.slice(9, 11), 10)
  if (number <= 1001998) return true
  let sum = 0
  for (let i = 0; i < 9; i++) sum += parseInt(d[i], 10) * (9 - i)
  let control
  if (sum < 100) control = sum
  else if (sum === 100 || sum === 101) control = 0
  else control = (sum % 101) === 100 ? 0 : (sum % 101)
  return control === checkSum
}

function weightedMod(value, weights) {
  let sum = 0
  for (let i = 0; i < weights.length; i++) sum += weights[i] * parseInt(value[i], 10)
  return (sum % 11) % 10
}

function validateInn10(v) {
  if (isEmpty(v)) return true
  const s = String(v).trim()
  if (!/^\d{10}$/.test(s)) return false
  return weightedMod(s, [2, 4, 10, 3, 5, 9, 4, 6, 8]) === parseInt(s[9], 10)
}

function validateInn12(v) {
  if (isEmpty(v)) return true
  const s = String(v).trim()
  if (!/^\d{12}$/.test(s)) return false
  const c1 = weightedMod(s, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8])
  const c2 = weightedMod(s, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8])
  return c1 === parseInt(s[10], 10) && c2 === parseInt(s[11], 10)
}

/**
 * Контрольная цифра ОГРН/ОГРНИП для больших чисел (превышают Number.MAX_SAFE_INTEGER).
 * Используем строковое деление столбиком: возвращает (value mod divisor) как целое число.
 */
function modString(value, divisor) {
  let r = 0
  for (const ch of value) r = (r * 10 + parseInt(ch, 10)) % divisor
  return r
}

function validateOgrn(v) {
  if (isEmpty(v)) return true
  const s = String(v).trim()
  if (!/^\d{13}$/.test(s)) return false
  return (modString(s.slice(0, 12), 11) % 10) === parseInt(s[12], 10)
}

function validateOgrnip(v) {
  if (isEmpty(v)) return true
  const s = String(v).trim()
  if (!/^\d{15}$/.test(s)) return false
  return (modString(s.slice(0, 14), 13) % 10) === parseInt(s[14], 10)
}

function validateKpp(v) {
  if (isEmpty(v)) return true
  const s = String(v).trim().toUpperCase()
  return /^\d{4}[A-Z\d]{2}\d{3}$/.test(s)
}

function validateBirthDate(v) {
  if (isEmpty(v)) return true
  const m = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(String(v).trim())
  if (!m) return false
  const day = parseInt(m[1], 10)
  const month = parseInt(m[2], 10)
  const year = parseInt(m[3], 10)
  const date = new Date(year, month - 1, day)
  if (
    date.getFullYear() !== year ||
    date.getMonth() !== month - 1 ||
    date.getDate() !== day
  ) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  if (date > today) return false
  const age = (today - date) / (1000 * 60 * 60 * 24 * 365.25)
  return age <= 120
}

const re = (regex) => (v) => isEmpty(v) || regex.test(digits(v))

export const FORM_FIELD_VALIDATIONS = [
  {
    value: 'snils',
    label: 'СНИЛС',
    message: 'Укажите корректный СНИЛС (11 цифр, формат «XXX-XXX-XXX YY»).',
    validate: validateSnils,
  },
  {
    value: 'passport_series_rf',
    label: 'Серия паспорта РФ',
    message: 'Серия паспорта должна содержать ровно 4 цифры.',
    validate: re(/^\d{4}$/),
  },
  {
    value: 'passport_number_rf',
    label: 'Номер паспорта РФ',
    message: 'Номер паспорта должен содержать ровно 6 цифр.',
    validate: re(/^\d{6}$/),
  },
  {
    value: 'inn_personal',
    label: 'ИНН (физ. лицо)',
    message: 'Укажите корректный ИНН физического лица (12 цифр).',
    validate: validateInn12,
  },
  {
    value: 'inn_company',
    label: 'ИНН (юр. лицо)',
    message: 'Укажите корректный ИНН юридического лица (10 цифр).',
    validate: validateInn10,
  },
  {
    value: 'ogrn',
    label: 'ОГРН',
    message: 'Укажите корректный ОГРН (13 цифр).',
    validate: validateOgrn,
  },
  {
    value: 'ogrnip',
    label: 'ОГРНИП',
    message: 'Укажите корректный ОГРНИП (15 цифр).',
    validate: validateOgrnip,
  },
  {
    value: 'kpp',
    label: 'КПП',
    message: 'Укажите корректный КПП (9 символов в формате «NNNN##NNN»).',
    validate: validateKpp,
  },
  {
    value: 'birth_date',
    label: 'Дата рождения',
    message: 'Укажите корректную дату рождения в формате ДД.ММ.ГГГГ.',
    validate: validateBirthDate,
  },
  {
    value: 'postal_code_rf',
    label: 'Почтовый индекс РФ',
    message: 'Почтовый индекс должен содержать ровно 6 цифр.',
    validate: re(/^\d{6}$/),
  },
]

const presetByValue = Object.fromEntries(FORM_FIELD_VALIDATIONS.map((p) => [p.value, p]))

export function getFormFieldValidationPreset(value) {
  if (!value) return null
  return presetByValue[value] || null
}

/**
 * Проверяет значение по ключу пресета.
 * @returns {string|null} текст ошибки или null если валидно
 */
export function validateFormFieldByPreset(presetValue, value) {
  const preset = getFormFieldValidationPreset(presetValue)
  if (!preset) return null
  return preset.validate(value) ? null : preset.message
}
