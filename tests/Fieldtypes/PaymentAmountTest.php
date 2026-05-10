<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ThomasVantuycom\StatamicMollie\Fieldtypes\PaymentAmount;
use ThomasVantuycom\StatamicMollie\Rules\PaymentAmount as PaymentAmountRule;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class PaymentAmountTest extends TestCase
{
    #[Test]
    public function it_is_not_selectable(): void
    {
        $fieldtype = new PaymentAmount;

        $this->assertFalse($fieldtype->selectable());
        $this->assertFalse($fieldtype->selectableInForms());
    }

    #[Test]
    public function it_preloads_enabled_currencies(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR', 'USD'],
        ]);

        $this->assertSame([
            'currencies' => ['EUR', 'USD'],
        ], (new PaymentAmount)->preload());
    }

    #[Test]
    public function it_returns_the_default_value(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR', 'USD'],
            'statamic.mollie.default_currency' => 'USD',
        ]);

        $this->assertSame([
            'currency' => 'USD',
            'value' => null,
        ], (new PaymentAmount)->defaultValue());
    }

    #[Test]
    #[DataProvider('preProcessProvider')]
    public function it_preprocesses_values(mixed $value, array $expected): void
    {
        $this->assertSame($expected, (new PaymentAmount)->preProcess($value));
    }

    public static function preProcessProvider(): array
    {
        return [
            'null' => [
                null,
                ['currency' => null, 'value' => null],
            ],
            'string' => [
                'EUR 10.00',
                ['currency' => null, 'value' => null],
            ],
            'integer' => [
                10,
                ['currency' => null, 'value' => null],
            ],
            'float' => [
                10.00,
                ['currency' => null, 'value' => null],
            ],
            'non-associative array' => [
                ['EUR', '10.00'],
                ['currency' => null, 'value' => null],
            ],
            'associative array with missing keys' => [
                ['cur' => 'EUR', 'val' => '10.00'],
                ['currency' => null, 'value' => null],
            ],
            'associative array with missing currency' => [
                ['value' => '10.00'],
                ['currency' => null, 'value' => '10.00'],
            ],
            'associative array with missing value' => [
                ['currency' => 'EUR'],
                ['currency' => 'EUR', 'value' => null],
            ],
            'associative array with correct keys' => [
                ['currency' => 'EUR', 'value' => '10.00'],
                ['currency' => 'EUR', 'value' => '10.00'],
            ],
        ];
    }

    #[Test]
    public function it_validates_values(): void
    {
        $this->assertEquals([new PaymentAmountRule], (new PaymentAmount)->rules());
    }
}
