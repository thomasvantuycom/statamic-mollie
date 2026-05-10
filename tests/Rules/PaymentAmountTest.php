<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Rules;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ThomasVantuycom\StatamicMollie\Rules\PaymentAmount;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class PaymentAmountTest extends TestCase
{
    #[Test]
    #[DataProvider('validAmountProvider')]
    public function it_passes_valid_amounts(mixed $value): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR', 'JPY'],
        ]);

        $validator = Validator::make([
            'amount' => $value,
        ], [
            'amount' => [new PaymentAmount],
        ]);

        $this->assertTrue($validator->passes());
    }

    public static function validAmountProvider(): array
    {
        return [
            'two-decimal currency' => [
                ['currency' => 'EUR', 'value' => '10.00'],
            ],
            'zero-decimal currency' => [
                ['currency' => 'JPY', 'value' => '10'],
            ],
            'extra-whitespace' => [
                ['currency' => 'JPY', 'value' => ' 10 '],
            ],
            'antlers expression' => [
                ['currency' => 'EUR', 'value' => '{{ amount }}'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidAmountProvider')]
    public function it_fails_invalid_amounts(mixed $value): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR', 'JPY'],
        ]);

        $validator = Validator::make([
            'amount' => $value,
        ], [
            'amount' => [new PaymentAmount],
        ]);

        $this->assertFalse($validator->passes());
    }

    public static function invalidAmountProvider(): array
    {
        return [
            'null' => [
                null,
            ],
            'string' => [
                'EUR 10.00',
            ],
            'integer' => [
                10,
            ],
            'float' => [
                10.00,
            ],
            'non-associative array' => [
                ['EUR', '10.00'],
            ],
            'missing currency' => [
                ['value' => '10.00'],
            ],
            'missing value' => [
                ['currency' => 'EUR'],
            ],
            'unsupported currency' => [
                ['currency' => 'INVALID', 'value' => '10.00'],
            ],
            'disabled currency' => [
                ['currency' => 'USD', 'value' => '10.00'],
            ],
            'missing decimals' => [
                ['currency' => 'EUR', 'value' => '10'],
            ],
            'too few decimals' => [
                ['currency' => 'EUR', 'value' => '10.0'],
            ],
            'too many decimals' => [
                ['currency' => 'EUR', 'value' => '10.000'],
            ],
            'zero amount' => [
                ['currency' => 'EUR', 'value' => '0.00'],
            ],
            'comma decimal separator' => [
                ['currency' => 'EUR', 'value' => '10,00'],
            ],
            'negative amount' => [
                ['currency' => 'EUR', 'value' => '-10.00'],
            ],
            'decimals for zero-decimal currency' => [
                ['currency' => 'JPY', 'value' => '10.00'],
            ],
            'numeric zero-decimal amount value' => [
                ['currency' => 'JPY', 'value' => 10],
            ],
            'numeric two-decimal amount value' => [
                ['currency' => 'EUR', 'value' => 10.00],
            ],
        ];
    }

    #[Test]
    public function it_uses_translated_validation_messages(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR'],
        ]);

        $validator = Validator::make([
            'amount' => null,
        ], [
            'amount' => [new PaymentAmount],
        ]);

        $this->assertSame(
            'The amount must be a valid amount.',
            $validator->errors()->first('amount')
        );

        $validator = Validator::make([
            'amount' => ['currency' => 'INVALID', 'value' => '10.00'],
        ], [
            'amount' => [new PaymentAmount],
        ]);

        $this->assertSame(
            'The amount currency must be an enabled currency.',
            $validator->errors()->first('amount')
        );

        $validator = Validator::make([
            'amount' => ['currency' => 'EUR', 'value' => 10],
        ], [
            'amount' => [new PaymentAmount],
        ]);

        $this->assertSame(
            'The amount value must be a valid number.',
            $validator->errors()->first('amount')
        );

        $validator = Validator::make([
            'amount' => ['currency' => 'EUR', 'value' => '10'],
        ], [
            'amount' => [new PaymentAmount],
        ]);

        $this->assertSame(
            'The amount value must have 2 decimal places.',
            $validator->errors()->first('amount')
        );

        $validator = Validator::make([
            'amount' => ['currency' => 'EUR', 'value' => '0.00'],
        ], [
            'amount' => [new PaymentAmount],
        ]);

        $this->assertSame(
            'The amount value must be greater than zero.',
            $validator->errors()->first('amount')
        );
    }
}
