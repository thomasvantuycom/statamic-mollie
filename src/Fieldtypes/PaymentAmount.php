<?php

namespace ThomasVantuycom\StatamicMollie\Fieldtypes;

use Statamic\Fields\Fieldtype;
use ThomasVantuycom\StatamicMollie\Rules\PaymentAmount as PaymentAmountRule;
use ThomasVantuycom\StatamicMollie\Support\Currencies;

class PaymentAmount extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return [
            'currencies' => Currencies::enabled(),
        ];
    }

    public function defaultValue()
    {
        return [
            'currency' => Currencies::default(),
            'value' => null,
        ];
    }

    public function preProcess($data)
    {
        if (! is_array($data)) {
            return [
                'currency' => null,
                'value' => null,
            ];
        }

        return [
            'currency' => $data['currency'] ?? null,
            'value' => $data['value'] ?? null,
        ];
    }

    public function rules(): array
    {
        return [new PaymentAmountRule];
    }
}
