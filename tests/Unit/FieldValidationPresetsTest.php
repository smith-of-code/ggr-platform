<?php

namespace Tests\Unit;

use App\Services\Lms\Forms\FieldValidationPresets;
use Closure;
use PHPUnit\Framework\TestCase;

class FieldValidationPresetsTest extends TestCase
{
    /** @dataProvider validValues */
    public function test_valid_values(string $key, string $value): void
    {
        $rule = FieldValidationPresets::rule($key);
        $this->assertInstanceOf(Closure::class, $rule);

        $failed = false;
        $rule('test', $value, function () use (&$failed): void { $failed = true; });

        $this->assertFalse($failed, "Pre-set [$key] must accept value [$value]");
    }

    /** @dataProvider invalidValues */
    public function test_invalid_values(string $key, string $value): void
    {
        $rule = FieldValidationPresets::rule($key);
        $this->assertInstanceOf(Closure::class, $rule);

        $failed = false;
        $rule('test', $value, function () use (&$failed): void { $failed = true; });

        $this->assertTrue($failed, "Pre-set [$key] must reject value [$value]");
    }

    public function test_empty_value_is_skipped(): void
    {
        $rule = FieldValidationPresets::rule('snils');
        $failed = false;
        $rule('test', '', function () use (&$failed): void { $failed = true; });
        $this->assertFalse($failed);
    }

    public function test_unknown_preset_returns_null(): void
    {
        $this->assertNull(FieldValidationPresets::rule('unknown_xyz'));
        $this->assertFalse(FieldValidationPresets::exists('unknown_xyz'));
    }

    public function test_available_contains_all_keys(): void
    {
        $keys = array_keys(FieldValidationPresets::available());
        $expected = [
            'snils', 'passport_series_rf', 'passport_number_rf',
            'inn_personal', 'inn_company', 'ogrn', 'ogrnip', 'kpp',
            'birth_date', 'postal_code_rf',
        ];
        foreach ($expected as $k) {
            $this->assertContains($k, $keys);
        }
    }

    public static function validValues(): array
    {
        return [
            'snils plain' => ['snils', '11223344595'],
            'snils formatted' => ['snils', '112-233-445 95'],
            'passport_series_rf' => ['passport_series_rf', '4509'],
            'passport_number_rf' => ['passport_number_rf', '123456'],
            'inn_personal' => ['inn_personal', '500100732259'],
            'inn_company' => ['inn_company', '7707083893'],
            'ogrn' => ['ogrn', '1027700132195'],
            'ogrnip' => ['ogrnip', '304500116000157'],
            'kpp digits' => ['kpp', '770701001'],
            'kpp with letters' => ['kpp', '7707AB001'],
            'birth_date' => ['birth_date', '01.01.2000'],
            'postal_code_rf' => ['postal_code_rf', '101000'],
        ];
    }

    public static function invalidValues(): array
    {
        return [
            'snils too short' => ['snils', '123'],
            'snils wrong checksum' => ['snils', '11223344500'],
            'passport_series_rf 3 digits' => ['passport_series_rf', '450'],
            'passport_series_rf letters' => ['passport_series_rf', '45ab'],
            'passport_number_rf 5 digits' => ['passport_number_rf', '12345'],
            'inn_personal wrong checksum' => ['inn_personal', '500100732250'],
            'inn_personal 11 digits' => ['inn_personal', '50010073225'],
            'inn_company wrong checksum' => ['inn_company', '7707083890'],
            'inn_company 11 digits' => ['inn_company', '77070838933'],
            'ogrn wrong checksum' => ['ogrn', '1027700132190'],
            'ogrn 12 digits' => ['ogrn', '102770013219'],
            'ogrnip wrong checksum' => ['ogrnip', '304500116000150'],
            'kpp too short' => ['kpp', '77070100'],
            'kpp invalid chars' => ['kpp', '7707@!001'],
            'birth_date future' => ['birth_date', '01.01.2099'],
            'birth_date too old' => ['birth_date', '01.01.1800'],
            'birth_date wrong format' => ['birth_date', '2000-01-01'],
            'birth_date invalid date' => ['birth_date', '31.02.2000'],
            'postal_code_rf 5 digits' => ['postal_code_rf', '10100'],
        ];
    }
}
