<?php

namespace Tests\Unit;

use App\Services\Lms\Forms\FieldValidationPresets;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Проверяет, что Closure из FieldValidationPresets корректно работает в составе Laravel Validator
 * (как используется в FormPublicController::buildPublicFormAnswerRules).
 */
class FieldValidationPresetsValidatorTest extends TestCase
{
    private Factory $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $loader = new FileLoader(new Filesystem(), __DIR__);
        $translator = new Translator($loader, 'en');
        $this->validator = new Factory($translator);
    }

    public function test_snils_invalid_value_fails_validation(): void
    {
        $rule = FieldValidationPresets::rule('snils');

        $v = $this->validator->make(
            ['answers' => ['user_snils' => '111']],
            ['answers.user_snils' => ['nullable', $rule]],
        );

        $this->assertTrue($v->fails());
        $this->assertSame(
            FieldValidationPresets::message('snils'),
            $v->errors()->first('answers.user_snils'),
        );
    }

    public function test_snils_valid_value_passes(): void
    {
        $rule = FieldValidationPresets::rule('snils');

        $v = $this->validator->make(
            ['answers' => ['user_snils' => '11223344595']],
            ['answers.user_snils' => ['nullable', $rule]],
        );

        $this->assertFalse($v->fails(), $v->errors()->first('answers.user_snils'));
    }

    public function test_inn_company_invalid_value_fails(): void
    {
        $rule = FieldValidationPresets::rule('inn_company');

        $v = $this->validator->make(
            ['answers' => ['inn' => '7707083890']],
            ['answers.inn' => ['nullable', $rule]],
        );

        $this->assertTrue($v->fails());
    }

    public function test_passport_combo_validates_independently(): void
    {
        $seriesRule = FieldValidationPresets::rule('passport_series_rf');
        $numberRule = FieldValidationPresets::rule('passport_number_rf');

        $v = $this->validator->make(
            ['answers' => ['series' => '4509', 'number' => '12345']],
            [
                'answers.series' => ['nullable', $seriesRule],
                'answers.number' => ['nullable', $numberRule],
            ],
        );

        $this->assertTrue($v->fails());
        $this->assertFalse($v->errors()->has('answers.series'));
        $this->assertTrue($v->errors()->has('answers.number'));
    }
}
