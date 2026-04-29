<?php

namespace App\Services\Lms\Forms;

use Closure;
use DateTimeImmutable;

/**
 * Каталог предустановленных правил валидации для полей формы.
 *
 * Используется и серверной (FormPublicController) и админской (FormController) частью.
 * Зеркальный каталог на фронте — resources/js/constants/formFieldValidations.js.
 */
class FieldValidationPresets
{
    /**
     * Возвращает список доступных пресетов: ключ => человеко-читаемая метка.
     *
     * @return array<string, string>
     */
    public static function available(): array
    {
        return [
            'snils' => 'СНИЛС',
            'passport_series_rf' => 'Серия паспорта РФ',
            'passport_number_rf' => 'Номер паспорта РФ',
            'inn_personal' => 'ИНН (физ. лицо)',
            'inn_company' => 'ИНН (юр. лицо)',
            'ogrn' => 'ОГРН',
            'ogrnip' => 'ОГРНИП',
            'kpp' => 'КПП',
            'birth_date' => 'Дата рождения',
            'postal_code_rf' => 'Почтовый индекс РФ',
        ];
    }

    /**
     * Возвращает Closure для использования в Validator::make / $request->validate.
     * Closure пропускает пустые/null-значения (обязательность регулируется отдельно правилом required).
     */
    public static function rule(string $key): ?Closure
    {
        $message = self::message($key);

        return match ($key) {
            'snils' => self::wrap(fn ($v) => self::isValidSnils($v), $message),
            'passport_series_rf' => self::wrap(fn ($v) => (bool) preg_match('/^\d{4}$/', self::digits($v)), $message),
            'passport_number_rf' => self::wrap(fn ($v) => (bool) preg_match('/^\d{6}$/', self::digits($v)), $message),
            'inn_personal' => self::wrap(fn ($v) => self::isValidInn12((string) $v), $message),
            'inn_company' => self::wrap(fn ($v) => self::isValidInn10((string) $v), $message),
            'ogrn' => self::wrap(fn ($v) => self::isValidOgrn((string) $v), $message),
            'ogrnip' => self::wrap(fn ($v) => self::isValidOgrnip((string) $v), $message),
            'kpp' => self::wrap(fn ($v) => (bool) preg_match('/^\d{4}[A-Z\d]{2}\d{3}$/', mb_strtoupper((string) $v)), $message),
            'birth_date' => self::wrap(fn ($v) => self::isValidBirthDate((string) $v), $message),
            'postal_code_rf' => self::wrap(fn ($v) => (bool) preg_match('/^\d{6}$/', self::digits($v)), $message),
            default => null,
        };
    }

    public static function message(string $key): string
    {
        return match ($key) {
            'snils' => 'Укажите корректный СНИЛС (11 цифр, формат «XXX-XXX-XXX YY»).',
            'passport_series_rf' => 'Серия паспорта должна содержать ровно 4 цифры.',
            'passport_number_rf' => 'Номер паспорта должен содержать ровно 6 цифр.',
            'inn_personal' => 'Укажите корректный ИНН физического лица (12 цифр).',
            'inn_company' => 'Укажите корректный ИНН юридического лица (10 цифр).',
            'ogrn' => 'Укажите корректный ОГРН (13 цифр).',
            'ogrnip' => 'Укажите корректный ОГРНИП (15 цифр).',
            'kpp' => 'Укажите корректный КПП (9 символов в формате «NNNN##NNN»).',
            'birth_date' => 'Укажите корректную дату рождения в формате ДД.ММ.ГГГГ.',
            'postal_code_rf' => 'Почтовый индекс должен содержать ровно 6 цифр.',
            default => 'Значение не соответствует требуемому формату.',
        };
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::available());
    }

    /**
     * Оборачивает чистую проверку boolean(value): bool в Closure-формат Laravel.
     */
    private static function wrap(Closure $check, string $message): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($check, $message): void {
            if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                return;
            }
            if (! $check($value)) {
                $fail($message);
            }
        };
    }

    private static function digits(mixed $value): string
    {
        return preg_replace('/\D/u', '', (string) $value) ?? '';
    }

    /**
     * СНИЛС: 11 цифр + контрольная сумма (для номеров > 001-001-998).
     * Принимает как «12345678901», так и «123-456-789 01».
     */
    private static function isValidSnils(mixed $value): bool
    {
        $digits = self::digits($value);
        if (strlen($digits) !== 11) {
            return false;
        }

        $number = (int) substr($digits, 0, 9);
        $checkSum = (int) substr($digits, 9, 2);

        if ($number <= 1001998) {
            return true;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $digits[$i]) * (9 - $i);
        }

        $control = match (true) {
            $sum < 100 => $sum,
            $sum === 100, $sum === 101 => 0,
            default => $sum % 101 === 100 ? 0 : $sum % 101,
        };

        return $control === $checkSum;
    }

    /**
     * ИНН физического лица — 12 цифр + 2 контрольные суммы.
     */
    private static function isValidInn12(string $value): bool
    {
        $value = trim($value);
        if (! preg_match('/^\d{12}$/', $value)) {
            return false;
        }
        $w11 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
        $w12 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];

        $check1 = self::weightedMod($value, $w11);
        $check2 = self::weightedMod($value, $w12);

        return $check1 === (int) $value[10] && $check2 === (int) $value[11];
    }

    /**
     * ИНН юридического лица — 10 цифр + контрольная сумма.
     */
    private static function isValidInn10(string $value): bool
    {
        $value = trim($value);
        if (! preg_match('/^\d{10}$/', $value)) {
            return false;
        }
        $weights = [2, 4, 10, 3, 5, 9, 4, 6, 8];
        $check = self::weightedMod($value, $weights);

        return $check === (int) $value[9];
    }

    /**
     * ОГРН — 13 цифр; контрольная цифра = (число из первых 12 цифр) mod 11, mod 10.
     */
    private static function isValidOgrn(string $value): bool
    {
        $value = trim($value);
        if (! preg_match('/^\d{13}$/', $value)) {
            return false;
        }
        $base = substr($value, 0, 12);
        $control = ((int) bcmod($base, '11')) % 10;

        return $control === (int) $value[12];
    }

    /**
     * ОГРНИП — 15 цифр; контрольная цифра = (число из первых 14 цифр) mod 13, mod 10.
     */
    private static function isValidOgrnip(string $value): bool
    {
        $value = trim($value);
        if (! preg_match('/^\d{15}$/', $value)) {
            return false;
        }
        $base = substr($value, 0, 14);
        $control = ((int) bcmod($base, '13')) % 10;

        return $control === (int) $value[14];
    }

    /**
     * Дата рождения: ДД.ММ.ГГГГ, валидная дата, не в будущем, возраст ≤ 120 лет.
     */
    private static function isValidBirthDate(string $value): bool
    {
        $value = trim($value);
        if (! preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $value, $m)) {
            return false;
        }
        [$day, $month, $year] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        if (! checkdate($month, $day, $year)) {
            return false;
        }
        $date = DateTimeImmutable::createFromFormat('d.m.Y', $value);
        if ($date === false) {
            return false;
        }
        $now = new DateTimeImmutable('today');
        if ($date > $now) {
            return false;
        }
        $age = $now->diff($date)->y;

        return $age <= 120;
    }

    /**
     * @param  list<int>  $weights
     */
    private static function weightedMod(string $digits, array $weights): int
    {
        $sum = 0;
        $count = count($weights);
        for ($i = 0; $i < $count; $i++) {
            $sum += $weights[$i] * (int) $digits[$i];
        }

        return ($sum % 11) % 10;
    }
}
