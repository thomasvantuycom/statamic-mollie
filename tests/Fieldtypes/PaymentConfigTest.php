<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Group;
use ThomasVantuycom\StatamicMollie\Fieldtypes\PaymentConfig;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class PaymentConfigTest extends TestCase
{
    #[Test]
    public function it_extends_the_group_fieldtype()
    {
        $this->assertInstanceOf(Group::class, new PaymentConfig);
    }

    #[Test]
    public function it_is_not_selectable(): void
    {
        $fieldtype = new PaymentConfig;

        $this->assertFalse($fieldtype->selectable());
        $this->assertFalse($fieldtype->selectableInForms());
    }

    #[Test]
    #[DataProvider('preProcessProvider')]
    public function it_sets_enabled_during_preprocess(mixed $value, bool $expected): void
    {
        $field = (new PaymentConfig)->setField(new Field('payment', [
            'type' => 'payment_config',
            'fields' => [
                [
                    'handle' => 'description',
                    'field' => ['type' => 'text'],
                ],
                [
                    'handle' => 'amount',
                    'field' => ['type' => 'payment_amount'],
                ],
            ],
        ]));

        $result = $field->preProcess($value);

        $this->assertArrayHasKey('enabled', $result);
        $this->assertSame($expected, $result['enabled']);
    }

    public static function preProcessProvider(): array
    {
        return [
            'no value' => [
                null,
                false,
            ],
            'set value' => [
                [
                    'description' => 'Test',
                    'amount' => [
                        'currency' => 'EUR',
                        'value' => '10.00',
                    ],
                ],
                true,
            ],
        ];
    }

    #[Test]
    public function it_removes_enabled_during_process(): void
    {
        $field = (new PaymentConfig)->setField(new Field('payment', [
            'type' => 'payment_config',
            'fields' => [
                [
                    'handle' => 'description',
                    'field' => ['type' => 'text'],
                ],
                [
                    'handle' => 'amount',
                    'field' => ['type' => 'payment_amount'],
                ],
            ],
        ]));

        $result = $field->process([
            'enabled' => true,
            'description' => 'Test',
            'amount' => ['currency' => 'EUR', 'value' => '10.00'],
        ]);

        $this->assertSame([
            'description' => 'Test',
            'amount' => ['currency' => 'EUR', 'value' => '10.00'],
        ], $result);
    }
}
